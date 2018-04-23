<?php
/**
 * Created by PhpStorm.
 *
 * PHP version 7
 *
 * @category Framework
 * @package  Slim3app
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://git.evrima.net/slim3app
 */
if (PHP_SAPI == 'cli-server') {
    $file = __DIR__ . $_SERVER['REQUEST_URI'];
    if (is_file($file)) {
        return false;
    }
}

define('_DROOT', dirname(__DIR__));

require _DROOT.'/vendor/autoload.php';

try {
    \App\Base::run();
} catch(Exception $e) {
    trigger_error($e->getMessage());
}
