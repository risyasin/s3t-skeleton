<?php
/**
 * Created by PhpStorm.
 *
 * PHP version 7
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */


if (!defined('_DROOT')) {
    exit(1);
}

return [
    'project' => [
        'name' => 'Slim3 Skeleton App',
        'desc' => 'A Sample app with Slim3, Twig, Redbean & with a nice Admin UI',
        'email' => 'root@localhost'
    ],
    'settings' => [ // Slim3 uses only "settings".
        'view' => [
            'templatePath' => _DROOT.'/views',
            'twig' => [
                'cache' => _DROOT.'/tmp/twig-cache'
            ],
        ],
        'db' => [
            'default' => [
                'type' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'prod',
                'pass' => 'password',
                'db'   => 'prodDb',
                'freeze' => true
            ],
            'fs' => [ // additional databases
                'type' => 'sqlite',
                'path' => _DROOT.'/app/data/base.db',
                'user' => 'prod',
                'pass' => 'password',
                'freeze' => true
            ]
        ],
        'phpmailer' => [
            'auth'   => true,
            'secure' => 'tls',
            'host'   => '127.0.0.1',
            'port'   => 587,
            'user'   => 'root@localhost',
            'pass'   => 'password',
            'from'   => 'App @ test.com',
            'debug'  => 3
        ],
        'locale'=> [
            'fallback' => 'en_US',  // Default locale.
            'available' => ['tr_TR', 'en_US'], // installed locales
            'switch' => 'hl', // hl=en will change to language to en_US
            'dumpPath' => '/__dumpGettextStr' // to dump all gettext slugs
        ],
        // Module loader switch.
        'modules' => [ 'auth', 'admin' ]
    ],
    // -------------------------- //
    // module configuration
    'auth' => [
        // if there no user in db. this will be added
        // as it's default values. username: admin & password: pass
        'defaultUser' => [ 'admin', 'pass', 'Default User', 'root@localhost' ],
        'successRoute' => 'admin.home',
        'maxWrongPass' => 5
    ]
];