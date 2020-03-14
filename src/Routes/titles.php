<?php

declare(strict_types=1);

$router->get('titles/', function () use ($app) {
    $titles = $app->getTitles();
    require_once '../src/View/titles/index.php';
});

$router->get('titles/{id}', function ($id) use ($app) {
    $isLogged = $app->getIsLogged();
    $title = $app->getTitle((int) $id);
    require_once '../src/View/titles/view.php';
});

$router->get('titles/add', function () {
    require_once '../src/View/titles/add.php';
});

$router->post('titles/add', function () use ($app) {
    $id = $app->addTitle($_POST['title']);
    $app->addEntry($id, $_POST['content']);
    header("Location: /titles/$id");
});

$router->get('titles/{id}/edit', function ($id) use ($app) {
    $title = $app->getTitle((int) $id);
    require_once '../src/View/titles/edit.php';
});

$router->post('titles/{id}/edit', function ($id) use ($app) {
    $app->editTitle((int) $id, $_POST['title']);
    header("Location: /titles/$id");
});

$router->get('titles/{id}/delete', function ($id) use ($app) {
    $app->deleteTitle((int) $id);
    header("Location: /titles");
});

$router->get('titles/{id}/addEntry', function ($id) use ($app) {
    $title = $app->getTitle((int) $id);
    require_once '../src/View/entries/add.php';
});

$router->post('titles/{id}/addEntry', function ($id) use ($app) {
    $app->addEntry((int) $id, $_POST['content']);
    header("Location: /titles/$id");
});
