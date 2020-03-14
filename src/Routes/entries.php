<?php

declare(strict_types=1);

$router->get('entries/{id}', function ($id) use ($app) {
    $isLogged = $app->getIsLogged();
    $entry = $app->getEntry((int) $id);
    require_once '../src/View/entries/view.php';
});

$router->get('entries/{id}/edit', function ($id) use ($app) {
    $entry = $app->getEntry((int) $id);
    require_once '../src/View/entries/edit.php';
});

$router->post('entries/{id}/edit', function ($id) use ($app) {
    $app->editEntry((int) $id, $_POST['entry']);
    $entry = $app->getEntry((int) $id);
    $titleId = $entry['title_id'];
    header("Location: /titles/$titleId");
});

$router->get('entries/{id}/delete', function ($id) use ($app) {
    $entry = $app->getEntry((int) $id);
    $titleId = $entry['title_id'];
    $app->deleteEntry((int) $id);
    header("Location: /titles/$titleId");
});

$router->get('entries/{id}/addExample', function ($id) use ($app) {
    $entry = $app->getEntry((int) $id);
    require_once '../src/View/examples/add.php';
});

$router->post('entries/{id}/addExample', function ($id) use ($app) {
    $app->addExample((int) $id, $_POST['content']);
    $entry = $app->getEntry((int) $id);
    $titleId = $entry['title_id'];
    header("Location: /titles/$titleId");
});
