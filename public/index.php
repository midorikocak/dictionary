<?php

declare(strict_types=1);

namespace midorikocak\dictionary;

use PDO;

use function explode;
use function header;
use function parse_url;
use function session_start;
use function trim;

use const PHP_URL_PATH;

require '../vendor/autoload.php';

require '../src/View/layout/header.php';

$url = trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

$db = new PDO('sqlite:../data/database.db');

$app = new App($db);

$router = new Router();



require_once '../src/Routes/app.php';
require_once '../src/Routes/titles.php';
require_once '../src/Routes/entries.php';
require_once '../src/Routes/examples.php';

$router->run($_SERVER['REQUEST_METHOD'], $url);

require '../src/View/layout/footer.php';
