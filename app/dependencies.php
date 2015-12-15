<?php

use DebugBar\StandardDebugBar;

// DIC configuration
$container = $app->getContainer();


// Register Twig View helper
$container['view'] = function ($c) {
    /* @var $c \Slim\Container */
    $conf = (object) $c->get('settings')['view'];
    $twe = new Slim\Views\TwigExtension($c['router'],$c['request']->getUri());

    $view = new \Slim\Views\Twig($conf->templatePath, ['cache' => $conf->twig['cache']]);
    // Instantiate and add Slim specific extension
    $view->addExtension($twe);
    $view->addExtension(new Twig_Extension_Debug());

    return $view;
};


// -----------------------------------------------------------------------------
// Service factories
// -----------------------------------------------------------------------------


// monolog
$container['logger'] = function ($c) {
    /* @var $c \Slim\Container */
    $loggerConf = (object) $c->get('settings')['logger'];

    $logger = new Monolog\Logger($loggerConf->name);

    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($loggerConf->path, Monolog\Logger::DEBUG));

    return $logger;
};



// DebugBar
$container['debugbar'] = function ($c) {
    /* @var $c \Slim\Container */
    return new StandardDebugBar();
};


if (!empty($_SERVER['APP_ENV']) && $_SERVER['APP_ENV'] != 'development') {

    $container['errorHandler'] = function ($c) {
        /* @var $c \Slim\Container */
        /* @var $request Slim\Http\Request */
        /* @var $response Slim\Http\Response */
        /* @var $exception ErrorException */

        return function ($request, $response, $exception) use ($c) {

            $code = $exception->getCode();
            $message = $exception->getMessage();

            /* @var $logger Monolog\Logger */
            $logger = $c->get('logger');


            $logger->error($message.' at '.$exception->getFile().':'.$exception->getLine());
            $logger->addError($exception->getTraceAsString());

            return $c->get('response')->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode(['code' => $code, 'message' => $message]));
        };
    };



    $container['debugbarRenderer'] = function ($c) {
        /* @var $c \Slim\Container */
        return (object) ['render' => function () { return ''; }, 'renderHead' => function () { return ''; }];
    };


} else {


    $container['debugbarRenderer'] = function ($c) {
        /* @var $c \Slim\Container */
        // ->renderHead()
        // ->render()
        return $c->get('debugbar')->getJavascriptRenderer();
    };

    // Add DebugBar
    $app->add(new App\Middlewares\DebugBar($app->getContainer()));

}



// -----------------------------------------------------------------------------
// Action factories
// -----------------------------------------------------------------------------

$container['App\Action\Home'] = function ($c) {
    /* @var $c \Slim\Container */
    return new App\Action\Home($c);
};


