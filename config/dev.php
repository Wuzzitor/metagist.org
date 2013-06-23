<?php
/* 
 * Metagist dev environment configuration
 */

require __DIR__ . '/common.php';

ini_set('display_errors', 1);
error_reporting(-1);

// enable the debug mode
$app['debug'] = true;

/*
 * opauth configuration
 */
$app["opauth"] = array(
    "login" => "/auth/login",
    "callback" => "/auth/callback/github/oauth2callback",
    "config" => array(
        "security_salt" => "dev-salt",
        "Strategy" =>  array(
            "Github" => array(
                /* metagist.dev application */
                "client_id" => "0ef4e40f68dc5984133c",
                "client_secret" => "d9ebd46189cc3abc98741fd83694803a6a47fbbc"
            )
        )
    )
);

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => 'metagist',
    'user'     => 'root',
    'password' => '',
);

// User
$app['security.users'] = array(
    'admins' => 'bonndan'
);

/*
 * api config
 */
$app[\Metagist\Api\ServiceProvider::APP_WORKER_CONFIG] = array(
    'base_url' => 'http://metagist.dev/api/',
    'consumer_key' => 'dev-test',
    'consumer_secret' => 'dev-test',
);
$app[\Metagist\Api\ServiceProvider::APP_CONSUMERS] = array(
    'metagist.dev' => 'metagist.dev',
);
$app[\Metagist\Api\ServiceProvider::APP_SERVICES] = __DIR__ . '/services.json';