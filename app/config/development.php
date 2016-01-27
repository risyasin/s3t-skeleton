<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 16/12/15
 * Time: 01:45
 */

if (!defined('_DROOT')){ exit(1); }

return [
    'settings' => [
        'debugMode' => true,
        'displayErrorDetails' => true,
        'view' => [
            'twig' => [
                'cache' => false,
                'debug' => true
            ],
        ],
        'db' => [
            'default' => [
                'type' => 'mysql',
                'host' => '127.0.0.1',
                'user' => 'local',
                'pass' => 'local',
                'db'   => 'slim3',
                'freeze' => false
            ],
            'fs' => [ // additional databases
                'type' => 'sqlite',
                'path' => _DROOT.'/app/data/base.db',
                'user' => 'prod', // have to use prod password.
                'pass' => 'password',
                'freeze' => false
            ]
        ]
    ]
];
