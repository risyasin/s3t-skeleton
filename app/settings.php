<?php

if (!defined('_DROOT')){ exit(1); }

return [
   'debug' => true,
   'view' => [
      'templatePath' => _DROOT.'/views/backend',
      'twig' => [
         'cache' => _DROOT.'/tmp/twig-cache',
         'debug' => true,
         'auto_reload' => true,
      ],
   ],
    // monolog settings
   'logger' => [
      'name' => 'app',
      'path' => _DROOT.'/tmp/app.log',
   ],
];
