<?php
/**
 * Metagist.org
 */
ini_set('display_errors', 0);

require_once __DIR__ . '/../vendor/autoload.php';

$app = new Metagist\Application();
require __DIR__ . '/../src/app.php';
$app->run();
