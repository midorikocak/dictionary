<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use PDO;
use PHPUnit\Framework\TestCase;

use function password_hash;
use function reset;
use function session_destroy;
use function session_status;
use function strpos;

use const PASSWORD_DEFAULT;
use const PHP_SESSION_ACTIVE;

class AppTest extends TestCase
{
    private ?PDO $db;
    private App $app;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = new PDO('sqlite::memory:');
        $this->createTables();

        $this->app = new App($this->db);

        $this->app->users->register('midori', 'email@example.com', 'midoripass');
        $this->app->users->login('email@example.com', 'midoripass');
    }

    private function createTables(): void
    {
        $createUsers = "CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created DATETIME,
    modified DATETIME,
    UNIQUE (email, username)
);
";
        $createEntries = "
CREATE TABLE entries (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title_id INTEGER,
    content TEXT,
    created TIMESTAMP,
    modified TIMESTAMP
);
";
        $createTitles = "
CREATE TABLE titles (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(255) NOT NULL,
    created TIMESTAMP,
    modified TIMESTAMP,
    UNIQUE (title)
);
";
        $createExamples = "
CREATE TABLE examples (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    entry_id INTEGER,
    content TEXT,
    created TIMESTAMP,
    modified TIMESTAMP
);
";
        $passwordHash = password_hash('password', PASSWORD_DEFAULT);
        $insertUsers = "
INSERT INTO users (username, email, password, created, modified)
VALUES
('admin', 'admin@example.com', '$passwordHash', DATE(), DATE());
";

        $this->db->exec($createUsers);
        $this->db->exec($createTitles);
        $this->db->exec($createEntries);
        $this->db->exec($createExamples);
        $this->db->exec($insertUsers);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }

        $drop = "DROP TABLE users";
        $this->db->exec($drop);

        $this->db = null;

        unset($this->db);
    }

    public function testDatabaseCreated(): void
    {
        $stmt = $this->db->query("SELECT name
                                   FROM sqlite_master
                                   WHERE type = 'table'
                                   ORDER BY name");
        $tables = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tables[] = $row['name'];
        }
        $this->assertNotEmpty($tables);
    }

    public function testAddTitle(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $this->app->addTitle('Yazılım');

        $this->assertEquals(2, $this->db->lastInsertId());
    }

    public function testGetTitles(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $this->app->addTitle('Yazılım');

        $this->assertNotEmpty($this->app->getTitles());
    }

    public function testAddEntry(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();

        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $titleData = $this->app->getTitle($id);
        $entryData = reset($titleData['entries']);

        $this->assertTrue((bool) strpos($entryData['content'], 'işlev'));
    }

    public function testEditTitle(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();

        $this->app->editTitle((int) $id, 'Object Oriented');
        $titleData = $this->app->getTitle($id);
        $this->assertEquals('Object Oriented', $titleData['title']);
    }

    public function testdeleteTitle(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();

        $this->app->deleteTitle((int) $id);
        $this->expectException('Exception');
        $this->app->getTitle($id);
    }

    public function testSearchTitle(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();
        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addTitle('yazılımı');
        $id = $this->db->lastInsertId();
        $this->app->addEntry((int) $id, 'nesneli yazılım');

        $titleData = $this->app->search('Nesne');

        $this->assertNotEmpty($titleData);
    }

    public function testGetEntries(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();

        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $id, 'Cogito Ergo sum');

        $entries = $this->app->getEntries();

        $this->assertCount(2, $entries);
    }

    public function testGetEntry(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();

        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $id, 'Cogito Ergo sum');

        $entryId = $this->db->lastInsertId();
        $entryData = $this->app->getEntry((int) $entryId);
        $this->assertEquals('Cogito Ergo sum', $entryData['content']);
    }

    public function testEditEntry(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $id = $this->db->lastInsertId();
        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $id, 'Her başka nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $entries = $this->app->titles->entries->readEntriesByTitleId((int) $id);
        $entry = reset($entries);
        $this->app->editEntry((int) $entry->getId(), 'Edited Entry');
        $entryData = $this->app->getEntry($entry->getId());
        $this->assertEquals('Edited Entry', $entryData['content']);
    }

    public function testdeleteEntry(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama 2');
        $id = $this->db->lastInsertId();

        $this->app->addEntry((int) $id, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $entryId = $id = $this->db->lastInsertId();

        $this->app->deleteEntry((int) $entryId);
        $this->expectException('Exception');
        $this->app->getEntry((int) $entryId);
    }

    public function testGetExamples(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $titleId = $this->db->lastInsertId();

        $this->app->addEntry((int) $titleId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $titleId, 'Her başka nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $entryId = $this->db->lastInsertId();

        $this->app->addExample((int) $entryId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addExample((int) $entryId, 'Cogito Ergo sum');

        $entries = $this->app->getExamples();

        $this->assertCount(2, $entries);
    }

    public function testGetExample(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $titleId = $this->db->lastInsertId();

        $this->app->addEntry((int) $titleId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $titleId, 'Her başka nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $entryId = $this->db->lastInsertId();

        $this->app->addExample((int) $entryId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addExample((int) $entryId, 'Cogito Ergo sum');

        $exampleId = $this->db->lastInsertId();
        $exampleData = $this->app->getExample((int) $exampleId);
        $this->assertEquals('Cogito Ergo sum', $exampleData['content']);
    }

    public function testEditExample(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $titleId = $this->db->lastInsertId();

        $this->app->addEntry((int) $titleId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $titleId, 'Her başka nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $entryId = $this->db->lastInsertId();

        $this->app->addExample((int) $entryId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addExample((int) $entryId, 'Cogito Ergo sum');

        $exampleId = $this->db->lastInsertId();

        $this->app->editExample((int) $exampleId, 'Edited Example');
        $exampleData = $this->app->getExample((int) $exampleId);
        $this->assertEquals('Edited Example', $exampleData['content']);
    }

    public function testdeleteExample(): void
    {
        $this->app->addTitle('Nesne yönelimli programlama');
        $titleId = $this->db->lastInsertId();

        $this->app->addEntry((int) $titleId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addEntry((int) $titleId, 'Her başka nesneler olarak soyutlandığı bir programlama yaklaşımı.');

        $entryId = $this->db->lastInsertId();

        $this->app->addExample((int) $entryId, 'Her işlevin nesneler olarak soyutlandığı bir programlama yaklaşımı.');
        $this->app->addExample((int) $entryId, 'Cogito Ergo sum');

        $exampleId = $this->db->lastInsertId();

        $this->app->deleteExample((int) $exampleId);
        $this->expectException('Exception');
        $this->app->getExample((int) $exampleId);
    }
}
