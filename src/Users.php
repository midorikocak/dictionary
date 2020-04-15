<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use Exception;
use PDO;

use function filter_var;
use function password_hash;
use function password_verify;
use function strlen;

use const FILTER_VALIDATE_EMAIL;
use const PASSWORD_DEFAULT;

class Users
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->db = $db;
    }

    public function getUserByUsername(string $username): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username =  :username ");

        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        return $user;
    }

    public function getUserByEmail(string $email): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email =  :email ");

        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            return [];
        }

        return $user;
    }

    /**
     * @throws Exception
     */
    public function register(string $username, string $email, string $rawPassword): void
    {
        $password = password_hash($rawPassword, PASSWORD_DEFAULT);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email');
        }

        if (strlen($rawPassword) < 6) {
            throw new Exception('Invalid password');
        }

        $sql = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username, ':email' => $email, ':password' => $password]);
    }

    public function login(string $email, string $password): bool
    {
        $user = $this->getUserByEmail($email);

        if (password_verify($password, $user['password'] ?? '')) {
            $_SESSION['user'] = $user['username'];
            return true;
        }

        return false;
    }

    public function logout(): void
    {
        $_SESSION['user'] = null;
        unset($_SESSION['user']);
    }

    public function checkLogin(): bool
    {
        if (!isset($_SESSION['user'])) {
            throw new Exception('Unauthorized');
        }
        return true;
    }

    public function isLogged(): bool
    {
        return isset($_SESSION['user']);
    }

    public function changeUsername(string $newUsername): void
    {
        $this->checkLogin();
        $user = $this->getUserByUsername($_SESSION['user']);

        $user['username'] = $newUsername;

        $sql = "UPDATE users SET username=:username WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user['id'], ':username' => $newUsername]);

        $_SESSION['user'] = $newUsername;
    }

    public function changeEmail(string $newEmail): void
    {
        $this->checkLogin();
        $user = $this->getUserByUsername($_SESSION['user']);

        $user['email'] = $newEmail;

        $sql = "UPDATE users SET email=:email WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user['id'], ':email' => $user['email']]);
    }

    public function changePassword($newPassword): void
    {
        $this->checkLogin();
        $user = $this->getUserByUsername($_SESSION['user']);

        $user['password'] = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users SET password=:password WHERE id=:id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $user['id'], ':password' => $user['password']]);
    }
}
