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
    'settings' => [
        'debugMode' => true,
        'handleExceptions' => false,
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
