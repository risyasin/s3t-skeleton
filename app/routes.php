<?php


$app = App\Base::$app;

// Routes

$app->get('/', 'App\Action\Home:index')->setName('home');
