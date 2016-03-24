<?php


$app = App\Base::$app;

// Routes

$app->get('/', 'App\Actions\Home:index')->setName('home');
