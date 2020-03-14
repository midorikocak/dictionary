<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;

use function array_walk;
use function reset;

class Titles
{
    private PDO $db;
    public Entries $entries;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->entries = new Entries($db);
    }

    /**
     * @return Title[]
     */
    public function readAll(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM titles LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $titles = [];
        foreach ($results as $titleData) {
            $title = new Title($titleData['title'], (int) $titleData['id']);
            $entries = $this->entries->readEntriesByTitleId((int) $titleData['id']);
            array_walk($entries, function ($entry) use (&$title) {
                $title->addEntry($entry);
            });
            $titles [] = $title;
        }

        return $titles;
    }

    /**
     * @return Title[]
     */
    public function readByTitle(string $title, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM titles WHERE title=:title LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':title' => $title, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $titles = [];
        foreach ($results as $titleData) {
            $title = new Title($titleData['title'], (int) $titleData['id']);
            $entries = $this->entries->readEntriesByTitleId((int) $titleData['id']);
            array_walk($entries, function ($entry) use (&$title) {
                $title->addEntry($entry);
            });
            $titles [] = $title;
        }

        return $titles;
    }

    /**
     * @return Title[]
     */
    public function search(string $keyword, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        if ($keyword === '') {
            return $this->readAll($page, $limit, $offset);
        }
        $title = '%' . $keyword . '%';
        $stmt = $this->db->prepare("SELECT * FROM titles WHERE title LIKE :title LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':title' => $title, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $titles = [];
        foreach ($results as $titleData) {
            $title = new Title($titleData['title'], (int) $titleData['id']);
            $entries = $this->entries->readEntriesByTitleId((int) $title->getId());
            array_walk($entries, function ($entry) use (&$title) {
                $title->addEntry($entry);
            });
            $titles[$titleData['id']] = $title;
        }

        return $titles;
    }

    public function read($id): Title
    {
        $stmt = $this->db->prepare("SELECT * FROM titles WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception('Not found');
        }
        $title = new Title($result['title'], (int) $result['id']);

        $entries = $this->entries->readEntriesByTitleId((int) $result['id']);
        array_walk($entries, function ($entry) use ($title) {
            $title->addEntry($entry);
        });

        return $title;
    }

    public function save(Title $title): int
    {
        $titleData = $title->getTitle();
        $titles = $this->readByTitle($titleData);
        $existingTitle = reset($titles);
        $id = $title->getId();
        if ($existingTitle) {
            $id = $existingTitle->getId();
        }

        if ($id) {
            $sql = "UPDATE titles SET title=:title WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $id, ':title' => $title->getTitle()]);
            return $id;
        }

        $data = $title->toArray();
        $sql = "INSERT INTO titles (title) VALUES (:title)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['title' => $data['title']]);
        return (int) $this->db->lastInsertId();
    }

    public function remove(Title $title): void
    {
        if ($title->getId()) {
            $sql = "DELETE FROM titles WHERE id=:title_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['title_id' => $title->getId()]);
        }
    }
}
