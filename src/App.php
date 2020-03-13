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

    private bool $isLogged;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->titles = new Titles($db);
        $this->isLogged = false;
    }

    public function login($username, $password)
    {
        session_start();
        if ($username === 'midori' && $password === 'midoripass') {
            $_SESSION['user'] = 'midori';
            $this->isLogged = true;
        }
    }

    public function logout()
    {
        unset($_SESSION['user']);
        $this->isLogged = false;
        session_destroy();
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

    public function checkLogin()
    {
        if (!$this->isLogged) {
            throw new Exception('Unauthorized');
        }
    }

    public function getIsLogged(): bool
    {
        return $this->isLogged;
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

    public function search(string $keyword, $page = 0, $limit = 10, $offset = 0): array
    {
        if ($keyword === '') {
            return $this->getTitles($page, $limit, $offset);
        }
        $titles = $this->titles->search(strtolower($keyword), $page, $limit, $offset);
        $entries = $this->titles->entries->search(strtolower($keyword), $page, $limit, $offset);
        $titleIds = array_map(fn($entry) => $entry->getTitleId(), $entries);
        array_walk($titleIds, function ($id) use (&$titles) {
            if (!isset($titles[$id])) {
                $titles[$id] = $this->titles->read($id);
            }
        });
        return array_map(fn($title) => $title->toArray(), $titles);
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
}
