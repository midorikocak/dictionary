<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;

class Entries
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function readEntriesByTitleId(int $titleId, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM entries WHERE title_id=:title_id LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':title_id' => $titleId, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entries = [];
        foreach ($results as $entryData) {
            $entry = new Entry($entryData['content'], (int) $entryData['id'], (int) $entryData['title_id']);
            $entries [] = $entry;
        }

        return $entries;
    }

    /**
     * @return Entry[]
     */
    public function readAll(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM entries LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entries = [];
        foreach ($results as $key => $value) {
            $entry = new Entry($value['content'], (int) $key, (int) $value['title_id']);
            $entries [] = $entry;
        }

        return $entries;
    }

    /**
     * @return Entry[]
     */
    public function search(string $keyword, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $keyword = '%' . $keyword . '%';
        $stmt = $this->db->prepare("SELECT * FROM entries WHERE content LIKE :keyword LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':keyword' => $keyword, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $entries = [];
        foreach ($results as $key => $value) {
            $entry = new Entry($value['content'], (int) $key, (int) $value['title_id']);
            $entries [$value['title_id']] = $entry;
        }

        return $entries;
    }

    public function read($id): Entry
    {
        $stmt = $this->db->prepare("SELECT * FROM entries WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception('Not found');
        }
        return new Entry($result['content'], (int) $result['id'], (int) $result['title_id']);
    }

    public function save(Entry $entry): void
    {
        if ($entry->getId()) {
            $sql = "UPDATE entries SET content=:content WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $entry->getId(), ':content' => $entry->getContent()]);
        } else {
            $data = $entry->toArray();
            $sql = "INSERT INTO entries (content, title_id) VALUES (:content, :titleId)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['content' => $data['content'], 'titleId' => $entry->getTitleId()]);
        }
    }

    public function remove(Entry $entry): void
    {
        if ($entry->getId()) {
            $data = $entry->toArray();
            $sql = "DELETE FROM entries WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $entry->getId()]);
        } else {
            throw new Exception('Not found');
        }
    }
}
