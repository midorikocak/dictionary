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
    if ($app->login($_POST['username'], $_POST['password'])) {
        header("Location: /");
    } else {
        header("Location: /login");
    }
});

$router->get('logout', function () use ($app) {
    $app->logout();
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

