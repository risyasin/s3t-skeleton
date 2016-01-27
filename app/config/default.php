<?php

if (!defined('_DROOT')){ exit(1); }

return [
    'project' => [
        'name' => 'Slim3 Skeleton App',
        'description' => 'A Sample app with Slim3, Twig, Redbead, Debugbar & with a nice Admin UI',
        'email' => 'root@localhost'
    ],
    'settings' => [ // Slim3 uses only "settings".
        'view' => [
            'templatePath' => _DROOT.'/views',
            'twig' => [
                'cache' => _DROOT.'/tmp/twig-cache'
            ],
        ],
        'monolog' => [
            'name' => 'app',
            'path' => _DROOT.'/tmp/app.log',
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
            'fallback' => 'en_US',  // Default locale. null means auto by browser lang
            'available' => ['tr_TR', 'en_US'], // installed locales
            'switch' => 'hl', // hl=en will change to language to en_US
            'dumpPath' => '/__dumpGettextStr' // url to be used to dump all gettext slugs
        ],
        // Module loader switch.
        'modules' => [ 'auth', 'admin' ]
    ],
    // -------------------------- //
    // module configuration
    'auth' => [
        // if there no user in db. this will be added
        // as it's default values. username: admin & pasword: pass
        'defaultUser' => [ 'admin', 'pass', 'Default User', 'root@localhost' ],
        'successPath' => 'admin.home',
        'maxWrongPass' => 5
    ]
];