<?php

if (PHP_SAPI == 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) { return false; }
}

define('_DROOT', dirname(__DIR__));

require(_DROOT.'/vendor/autoload.php');

// session_start();

$app = new \Slim\App(['settings' => require(_DROOT.'/app/settings.php')]);

require(_DROOT.'/app/dependencies.php');

require(_DROOT.'/app/routes.php');


$app->run();

