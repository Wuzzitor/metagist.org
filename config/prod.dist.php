<?php
/* 
 * Metagist productive environment configuration
 */

require __DIR__ . '/common.php';

$app['http_cache.enabled'] = true;

// Doctrine (db)
$app['db.options'] = array(
    'driver'   => 'pdo_mysql',
    'host'     => 'localhost',
    'dbname'   => '',
    'user'     => 'root',
    'password' => '',
);

/*
 * opauth configuration
 */
$app["opauth"] = array(
    "login" => "/auth/login",
    "callback" => "/auth/login/github/oauth2callback",
    "config" => array(
        "security_salt" => "",
        "Strategy" =>  array(
            "Github" => array(
                "client_id" => "",
                "client_secret" => ""
            )
        )
    )
);