<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 16/12/15
 * Time: 23:32
 */

namespace App;


use Slim\App as Slim3;
use Slim\Container;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class Manager
 * @package App
 */

class Base
{

    use BaseHelper;

    /* @var array $config */
    public static $cfg;

    /* @var array $modules */
    public static $modules;

    /* @var $logger \Monolog\Logger */
    public static $logger;

    /* @var $debugbar \DebugBar\StandardDebugBar  */
    public static $debugbar = false;

    /* @var $app \Slim\App */
    public static $app;

    /* @var $container \Slim\Container */
    public static $c;

    /* @var $request ServerRequestInterface */
    public static $request;

    /* @var $response ResponseInterface */
    public static $response;

    /* @var $locale string Default en_US */
    public static $locale = 'en_US';

    public static $currentState = 0;

    /* @var $data array */
    public static $_data = [];

    /* @var $_mreg array Module registry */
    public static $_mreg;

    /* @const string Modules' namespace */
    const moduleNS = 'App\\Modules\\';



    /**
     * App facade!
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     */
    public static function run()
    {

        Base::setupConfig();

        Base::$c = new Container(Base::$c);


        Base::registerMonolog();

        if (Base::$cfg['debugMode']){
            Base::registerDebugBar();
            Base::stateLog('App booting!');
            Base::setErrorHandler();
        }

        Base::setLocale();

        Base::registerDB();

        Base::registerTwig();

        //Base::defaultActions();

        Base::stateLog('Loading modules!');

        Base::setupModules();

        Base::stateLog('Loading Slim3!');

        // Finally Run Slim3
        Base::$app = new Slim3(Base::$c);

        // rewrite Container. wonder if it makes any performance impact.
        Base::$c = Base::$app->getContainer();

        Base::$request = Base::$app->getContainer()->get('request');

        Base::$response = Base::$app->getContainer()->get('response');

        Base::stateLog('PostApp state!');

        Base::postApp();

        if (count(Base::$_mreg) > 0){
            foreach(Base::$_mreg as $n => $m) {
                if ($m->instance && in_array('postApp', $m->methods)){
                    $m->instance->postApp(Base::$app, Base::$c);
                }
            }
        }

        Base::stateLog('Loading user routes!');

        include_once _DROOT.'/app/routes.php';

        Base::stateLog('Loading module routes!');

        if (count(Base::$_mreg) > 0){
            foreach(Base::$_mreg as $n => $m) {
                if ($m->instance && in_array('routes', $m->methods)){
                    $m->instance->routes(Base::$app);
                }
            }
        }

        Base::stateLog('Slim 3 Run!');

        register_shutdown_function(function () {
            // @Todo: implement a proper shutdown to handle new exceptions.
            // R::close();
            // Base::stateLog('Shutting down!');
        });

        Base::$app->run();

    }


}