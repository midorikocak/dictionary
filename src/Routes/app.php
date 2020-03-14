<?php

declare(strict_types=1);

$router->get('/', function () use ($app) {
    $titles = $app->getTitles();
    require_once '../src/View/titles/index.php';
});

$router->get('login', function () {
    require_once '../src/View/login.php';
});

$router->post('login', function () use ($app) {
    if ($app->users->login($_POST['email'], $_POST['password'])) {
        header("Location: /");
    } else {
        header("Location: /login");
    }
});

$router->get('settings', function () use ($app) {
    $user = $app->users->getUserByUsername($_SESSION['user']);
    require_once '../src/View/settings.php';
});

$router->post('/settings', function () use ($app) {
    $user = $app->users->getUserByUsername($_SESSION['user']);
    $newUsername = $_POST['username'];
    $newEmail = $_POST['email'];
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];

    if (!empty($newUsername) && $user['username'] !== $newUsername) {
        $app->users->changeUsername($newUsername);
    }

    if (!empty($newEmail) && $user['email'] !== $newEmail) {
        $app->users->changeEmail($newEmail);
    }

    if (!empty($password) && $password === $passwordRepeat) {
        $app->users->changePassword($password);
    } else {
        throw new Exception('Invalid password');
    }

    header("Location: /settings");
});

$router->get('register', function () {
    require_once '../src/View/register.php';
});

$router->post('register', function () use ($app) {
    if ($_POST['password'] !== $_POST['passwordRepeat']) {
        throw new Exception('Passwords does not match');
    }
    $app->users->register($_POST['username'], $_POST['email'], $_POST['password']);
    header("Location: /login");
});

$router->get('logout', function () use ($app) {
    $app->users->logout();
    header("Location: /");
});

$router->get('search/{keyword}', function ($keyword) use ($app) {
    $titles = $app->search($keyword);
    require_once '../src/View/titles/index.php';
});

$router->post('search', function () use ($app) {
    $titles = $app->search($_POST['keyword']);
    require_once '../src/View/titles/index.php';
});
