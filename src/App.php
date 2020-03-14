<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;

use function array_map;
use function array_walk;
use function session_destroy;
use function session_start;
use function strtolower;

class App
{
    private PDO $db;
    public Titles $titles;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->titles = new Titles($db);
        session_start();
    }

    public function login($username, $password): bool
    {
        if ($username === 'midori' && $password === 'midoripass') {
            $_SESSION['user'] = $username;
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        session_destroy();
    }

    /**
     * @throws Exception
     */
    public function checkLogin(): void
    {
        if (!$this->getIsLogged()) {
            throw new Exception('Unauthorized');
        }
    }

    public function getIsLogged(): bool
    {
        return ($_SESSION['user'] ?? '') === 'midori';
    }

    public function getTitles(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $titles = $this->titles->readAll($page, $limit, $offset);
        /**
         * @var Title $title
         */
        return array_map(fn($title) => $title->toArray(), $titles);
    }

    public function getTitle($titleId): array
    {
        return $this->titles->read((string) $titleId)->toArray() ?? [];
    }

    public function addTitle(string $title): int
    {
        $this->checkLogin();
        /**
         * @var Title $titleObject
         */
        $titleObject = new Title($title);
        return $this->titles->save($titleObject);
    }

    public function editTitle(int $titleId, string $title): void
    {
        $this->checkLogin();
        /**
         * @var Title $titleObject
         */
        $titleObject = $this->titles->read($titleId);
        $titleObject->setTitle($title);
        $this->titles->save($titleObject);
    }

    public function deleteTitle(int $titleId): void
    {
        $this->checkLogin();
        /**
         * @var Title $titleObject
         */
        $titleObject = $this->titles->read($titleId);
        $this->titles->remove($titleObject);
    }

    public function search(string $keyword, $page = 0, $limit = 10, $offset = 0): array
    {
        if ($keyword === '') {
            return $this->getTitles($page, $limit, $offset);
        }

        $titles = $this->titles->search(strtolower($keyword), $page, $limit, $offset);
        $entries = $this->titles->entries->search(strtolower($keyword), $page, $limit, $offset);
        $examples = $this->titles->entries->examples->search(strtolower($keyword), $page, $limit, $offset);

        $entryIds = array_map(fn($example) => $example->getEntryId(), $examples);

        array_walk($entryIds, function ($id) use (&$entries) {
            if (!isset($entries[$id])) {
                $entries[$id] = $this->titles->entries->read($id);
            }
        });

        $titleIds = array_map(fn($entry) => $entry->getTitleId(), $entries);
        array_walk($titleIds, function ($id) use (&$titles) {
            if (!isset($titles[$id])) {
                $titles[$id] = $this->titles->read($id);
            }
        });
        return array_map(fn($title) => $title->toArray(), $titles);
    }

    public function getEntries(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $entries = $this->titles->entries->readAll($page, $limit, $offset);
        /**
         * @var Entry $entry
         */
        return array_map(fn($entry) => $entry->toArray(), $entries);
    }

    public function getEntry(int $entryId): array
    {
        return $this->titles->entries->read($entryId)->toArray();
    }

    public function addEntry(int $titleId, $entryContent): int
    {
        $this->checkLogin();
        /**
         * @var Entry $entryObject
         */
        $entryObject = new Entry($entryContent);
        $entryObject->setTitleId($titleId);
        $this->titles->entries->save($entryObject);
        return (int) $this->db->lastInsertId();
    }

    public function editEntry(int $entryId, string $entryContent): void
    {
        $this->checkLogin();
        /**
         * @var Entry $entryObject
         */
        $entryObject = $this->titles->entries->read($entryId);
        $entryObject->setContent($entryContent);
        $this->titles->entries->save($entryObject);
    }

    public function deleteEntry(int $entryId): void
    {
        $this->checkLogin();
        $entryObject = $this->titles->entries->read($entryId);
        $this->titles->entries->remove($entryObject);
    }

    public function getExamples(int $page = 0, int $limit = 10, int $offset = 0): array
    {
        $examples = $this->titles->entries->examples->readAll($page, $limit, $offset);
        /**
         * @var Example $example
         */
        return array_map(fn($example) => $example->toArray(), $examples);
    }

    public function getExample(int $exampleId): array
    {
        return $this->titles->entries->examples->read($exampleId)->toArray();
    }

    public function addExample(int $entryId, $exampleContent): int
    {
        $this->checkLogin();
        /**
         * @var Example $exampleObject
         */
        $exampleObject = new Example($exampleContent);
        $exampleObject->setEntryId($entryId);
        $this->titles->entries->examples->save($exampleObject);
        return (int) $this->db->lastInsertId();
    }

    public function editExample(int $exampleId, string $exampleContent): void
    {
        $this->checkLogin();
        /**
         * @var Example $exampleObject
         */
        $exampleObject = $this->titles->entries->examples->read($exampleId);
        $exampleObject->setContent($exampleContent);
        $this->titles->entries->examples->save($exampleObject);
    }

    public function deleteExample(int $exampleId): void
    {
        $this->checkLogin();
        $exampleObject = $this->titles->entries->examples->read($exampleId);
        $this->titles->entries->examples->remove($exampleObject);
    }
}
