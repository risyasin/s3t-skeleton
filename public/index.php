<?php

if (PHP_SAPI == 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) { return false; }
}

define('_DROOT', dirname(__DIR__));

require _DROOT.'/vendor/autoload.php';

\App\Base::run();
