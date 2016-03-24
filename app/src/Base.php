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

namespace App;

use Slim\App as Slim3;
use Slim\Container;


/**
 * Class Manager
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */

class Base
{

    // Helper trait, All tooling stuff goes here!
    use Helper;

    /* @var array $cfg Holds config */
    public static $cfg;
    /* @var string $env Environment name */
    public static $env = 'production';
    /* @var string $dev Developer/Server name */
    public static $dev = false;
    /* @var array $modules */
    public static $modules;
    /* @var \Monolog\Logger $logger */
    public static $logger;
    /* @var \Slim\App $app */
    public static $app;
    /* @var \Slim\Container $container */
    public static $c;
    /* @var \Slim\Http\Request $request */
    public static $request;
    /* @var \Slim\Http\Response $response */
    public static $response;
    /* @var \Slim\Route $route */
    public static $route;
    /* @var array $routes Collects all routes by role in menu for logged in user */
    public static $routes;
    /* @var array $params */
    public static $params;
    /* @var \App\Models\User $user */
    public static $user;
    /* @var string $locale Default tr_TR */
    public static $locale = 'en_US';
    /* @var array $timing */
    public static $timing = [];
    /* @var array $hiveData */
    public static $hiveData = [];
    /* @var array $moduleRegistry Module registry */
    public static $moduleRegistry;

    /* @const string Modules namespace */
    const MODULE_NS = 'App\\Modules\\';

    /* @const string Models namespace */
    const MODEL_NS = 'App\\Models\\';


    /**
     * App facade!
     *
     * @throws \Exception
     * @throws \Slim\Exception\MethodNotAllowedException
     * @throws \Slim\Exception\NotFoundException
     *
     * @return null
     */
    public static function run()
    {

        // sets environment state
        if ($_SERVER['APP_ENV'] ?? false) {
            Base::$env = $_SERVER['APP_ENV'];
        }

        // sets developer state
        if ($_SERVER['APP_DEV'] ?? false) {
            Base::$dev = $_SERVER['APP_DEV'];
        }

        Base::setupConfig();

        Base::$c = new Container(Base::$c);

        Base::registerDebugger();

        if (Base::$cfg['debugMode'] ?? false) {
            error_reporting(E_ALL);
            ini_set('display_errors', true);
            //set_error_handler('App\Base::errorHandler');
            //set_exception_handler('App\Base::exceptionHandler');

        } else {
            error_reporting(0);
            ini_set('display_errors', false);
        }

        Base::setLocale();

        Base::registerDB();

        Base::registerTwig();

        Base::setupModules();

        // Finally Run Slim3
        Base::$app = new Slim3(Base::$c);

        // rewrite Container. wonder if it makes any performance impact.
        Base::$c = Base::$app->getContainer();

        Base::$request = Base::$app->getContainer()->get('request');

        Base::$response = Base::$app->getContainer()->get('response');

        Base::$params = Base::$app->getContainer()->get('request')->getParams();

        include_once _DROOT.'/app/routes.php';

        if (count(Base::$moduleRegistry) > 0) {
            foreach (Base::$moduleRegistry as $n => $m) {
                if ($m->instance && in_array('routes', $m->methods)) {
                    $m->instance->routes(Base::$app);
                }
            }
        }

        Base::postApp();

        if (count(Base::$moduleRegistry) > 0) {
            foreach (Base::$moduleRegistry as $n => $m) {
                if ($m->instance && in_array('postApp', $m->methods)) {
                    $m->instance->postApp(Base::$app, Base::$c);
                }
            }
        }

        register_shutdown_function(
            function () {
                // @Todo: implement a proper shutdown to handle new exceptions.
                // R::close();
                // Base::stateLog('Shutting down!');
            }
        );

        Base::$app->run();

    }


}