<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;
use PHPUnit\Framework\TestCase;

use function session_destroy;
use function session_status;

use const PHP_SESSION_ACTIVE;

class UsersTest extends TestCase
{
    private ?PDO $db;
    private Users $users;

    public function setUp(): void
    {
        parent::setUp();
        $this->db = new PDO('sqlite::memory:');
        $this->createTables();

        $this->users = new Users($this->db);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        $this->db = null;
    }

    private function createTables(): void
    {
        $sql = "
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    created DATETIME,
    modified DATETIME,
    UNIQUE (email, username)
);

INSERT INTO users (username, email, password, created, modified)
VALUES
('cakeuser', 'cakephp@example.com', 'secret', DATE(), DATE());

INSERT INTO users (username, email, password, created, modified)
VALUES
('user2', 'midori@example.com', 'verysecret', DATE(), DATE());

";

        $this->db->query($sql)->execute();
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

    /**
     * @throws Exception
     */
    public function testLoginAndRegister(): void
    {
        $this->users->register('midorikocak', 'mtkocak@gmail.com', '123456');
        $this->users->login('mtkocak@gmail.com', '123456');

        $this->assertEquals('midorikocak', $_SESSION['user']);
    }

    public function testLogout(): void
    {
        $this->users->register('midorikocak', 'mtkocak@gmail.com', '123456');
        $this->users->login('mtkocak@gmail.com', '123456');
        $this->users->logout();
        $this->expectException('Exception');
        $this->users->checkLogin();
    }

    public function testCheckLoginUnauthorized(): void
    {
        $this->expectException('Exception');
        $this->users->logout();
        $this->users->checkLogin();
    }

    public function testChangeEmail(): void
    {
        $this->users->register('midorikocak', 'mtkocak@gmail.com', '123456');
        $this->users->login('mtkocak@gmail.com', '123456');

        $this->users->changeEmail('mtkocak@mtkocak.net');

        $user = $this->users->getUserByEmail('mtkocak@mtkocak.net');
        $this->assertNotEmpty($user);
    }
}
