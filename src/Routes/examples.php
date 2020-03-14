<?php

declare(strict_types=1);

$router->get('examples/{id}', function ($id) use ($app) {
    $isLogged = $app->getIsLogged();
    $example = $app->getExample((int) $id);
    require_once '../src/View/examples/view.php';
});

$router->get('examples/{id}/edit', function ($id) use ($app) {
    $example = $app->getExample((int) $id);
    require_once '../src/View/examples/edit.php';
});

$router->post('examples/{id}/edit', function ($id) use ($app) {
    $app->editExample((int) $id, $_POST['content']);

    $example = $app->getExample((int) $id);
    $entry = $app->getEntry((int) $example['entry_id']);
    $titleId = $entry['title_id'];

    header("Location: /titles/$titleId");
});

$router->get('examples/{id}/delete', function ($id) use ($app) {
    $example = $app->getExample((int) $id);

    $entryId = $example['entry_id'];
    $entry = $app->getEntry((int) $entryId);
    $titleId = $entry['title_id'];

    $app->deleteExample((int) $id);
    header("Location: /titles/$titleId");
});
