<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;

class Examples
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function readExamplesByEntryId(int $entryId, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM examples WHERE entry_id=:entry_id LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':entry_id' => $entryId, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $examples = [];
        foreach ($results as $exampleData) {
            $example = new Example($exampleData['content'], (int) $exampleData['id'], (int) $exampleData['entry_id']);
            $examples [] = $example;
        }

        return $examples;
    }

    /**
     * @return Example[]
     */
    public function readAll(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $stmt = $this->db->prepare("SELECT * FROM examples LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $examples = [];
        foreach ($results as $key => $value) {
            $example = new Example($value['content'], (int) $key, (int) $value['entry_id']);
            $examples [] = $example;
        }

        return $examples;
    }

    /**
     * @return Example[]
     */
    public function search(string $keyword, int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $keyword = '%' . $keyword . '%';
        $stmt = $this->db->prepare("SELECT * FROM examples WHERE content LIKE :keyword LIMIT :limit OFFSET :offset");
        $offset = ($limit * $page) + $offset;
        $stmt->execute([':keyword' => $keyword, ':limit' => $limit, ':offset' => $offset]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $examples = [];
        foreach ($results as $key => $value) {
            $example = new Example($value['content'], (int) $key, (int) $value['entry_id']);
            $examples [$value['entry_id']] = $example;
        }

        return $examples;
    }

    public function read($id): Example
    {
        $stmt = $this->db->prepare("SELECT * FROM examples WHERE id=:id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new Exception('Not found');
        }
        return new Example($result['content'], (int) $result['id'], (int) $result['entry_id']);
    }

    public function save(Example $example): void
    {
        if ($example->getId()) {
            $sql = "UPDATE examples SET content=:content WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $example->getId(), ':content' => $example->getContent()]);
        } else {
            $data = $example->toArray();
            $sql = "INSERT INTO examples (content, entry_id) VALUES (:content, :entryId)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['content' => $data['content'], 'entryId' => $example->getEntryId()]);
        }
    }

    public function remove(Example $example): void
    {
        if ($example->getId()) {
            $data = $example->toArray();
            $sql = "DELETE FROM examples WHERE id=:id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':id' => $example->getId()]);
        } else {
            throw new Exception('Not found');
        }
    }
}
