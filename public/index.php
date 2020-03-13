<?php
declare(strict_types=1);

use midorikocak\dictionary\App;

require '../vendor/autoload.php';


require '../src/View/layout/header.php';

$url = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');


$db = new PDO('sqlite:../data/database.db');

$app = new App($db);

$path = explode('/', $url);
$subPath = $path[1] ?? '';
$subSubPath = $path[2] ?? '';
/**
 *  Paths:
 *  1. /
 *  2. /titles?page=1 getTitles($page = 0);
 *  3. /titles/3?page=2  getTitle($titleId);
 *  4. /entries/7?page=3 getEntry($entryId);
 *  5. /examples/9 getExample($id)
 *  6. POST /titles addTitle($title)
 *  7. POST /titles/3/entries addEntry($titleId, $entry)
 */

try {
    switch (reset($path)) {
        case 'titles':
            if (is_numeric($subPath)) {
                if ($subSubPath === 'edit') {
                    $id = $subPath;
                    $title = $app->getTitle((int)$id);
                    require_once '../src/View/titles/edit.php';
                } elseif ($subSubPath === 'delete') {
                    $id = $subPath;
                    $app->deleteTitle((int)$id);
                    header("Location: /titles");
                } elseif ($subSubPath === 'addEntry') {
                    if (!isset($_POST['content'])) {
                        $id = $subPath;
                        $title = $app->getTitle((int)$id);
                        require_once '../src/View/entries/add.php';
                    } else {
                        $id = $subPath;
                        $app->addEntry((int)$id, $_POST['content']);
                        header("Location: /titles/$id");
                    }
                } else {
                    $id = $subPath;
                    $isLogged = $app->getIsLogged();
                    $title = $app->getTitle((int)$id);
                    require_once '../src/View/titles/view.php';
                }
            } else if ($subPath === '') {
                $titles = $app->getTitles();
                require_once '../src/View/titles/index.php';
            } else if ($subPath === 'add') {
                $app->checkLogin();
                if (isset($_POST['title'])) {
                    $id = $app->addTitle($_POST['title']);
                    $app->addEntry($id, $_POST['content']);
                    header("Location: /titles/$id");
                }
                $titles = $app->getTitles();
                require_once '../src/View/titles/add.php';
            } else if ($subPath === 'edit') {
                $id = $_POST['id'];
                $app->editTitle((int)$id, $_POST['title']);
                header("Location: /titles/$id");
            }


            break;

        case 'search':
            $keyword = $subPath !== '' ? $subPath : $_POST['keyword'] ?? '';
            $titles = $app->search($keyword);
            require_once '../src/View/titles/index.php';
            break;
        case 'login':
            if (isset($_POST['username'], $_POST['password'])) {
                $app->login($_POST['username'], $_POST['password']);
                header("Location: /");
            } else {
                require_once '../src/View/login.php';
            }
            break;
        case 'logout':
            $app->logout();
            header("Location: /");
            break;
        case 'entries':
            if (is_numeric($subPath)) {
                if ($subSubPath === 'edit') {
                    $id = $subPath;
                    $entry = $app->getEntry((int)$id);
                    require_once '../src/View/entries/edit.php';
                } elseif ($subSubPath === 'delete') {
                    $id = $subPath;
                    $entry = $app->getEntry((int)$id);
                    $titleId = $entry['title_id'];
                    $app->deleteEntry((int)$id);
                    header("Location: /titles/$titleId");
                } else {
                    $id = $subPath;
                    $entry = $app->getEntry((int)$id);
                    require_once '../src/View/entries/view.php';
                }
            } else if ($subPath === 'edit') {
                $id = $_POST['id'];
                $app->editEntry((int)$id, $_POST['content']);
                $entry = $app->getEntry((int)$id);
                $titleId = $entry['title_id'];
                header("Location: /titles/$titleId");
            }
            break;
        default:
    }
} catch (Exception $e) {
    if ($e->getMessage() === 'Unauthorized') {
        header("Location: /login");
    }
}


require '../src/View/layout/footer.php';

?>
