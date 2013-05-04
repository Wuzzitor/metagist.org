<?php
/* 
 * Metagist dev environment configuration
 */

require __DIR__ . '/common.php';

ini_set('display_errors', 1);
error_reporting(-1);

// enable the debug mode
$app['debug'] = true;

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => '',
    'user'     => 'root',
    'password' => '',
);