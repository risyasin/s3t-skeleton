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

use Slim\Http\Response;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use RedBeanPHP\R as R;
use Tracy\Debugger;
use Tracy\Dumper;

/**
 * Class BaseHelper
 * Base Helper Trait as the name suggests, holds the various tooling methods
 * that would improper to place in Base itself. Refer to ZendOpcache documentation.
 *
 * @package App
 */
trait BaseHelper
{

    /**
     * Configuration parser
     * Reads & adds `app/config/default.php` by default.
     * this holds platform or developer agnostic settings such as project.name
     * Platform specific config can be set by env variable APP_ENV.
     * Applies if an environment given such as development or production
     * This holds platform specific configurations eg: `app/config/development.php`
     * Developer specific config can be set by env variable APP_DEV.
     * that merges Developer specific configuration eg: `app/config/yasin.php`
     *
     * @return null
     */
    final public static function setupConfig()
    {

        try {
            // Default config.
            $config = include_once _DROOT.'/app/config/default.php';

            // you can overwrite your setting by environment variable "APP_ENV".
            $envConfigFile = _DROOT.'/app/config/'.Base::$env.'.php';
            if (is_file($envConfigFile) && is_readable($envConfigFile)) {
                $envConfig = include_once $envConfigFile;
                if (is_array($envConfig)) {
                    $config = array_replace_recursive($config, $envConfig);
                }
            }

            // you can even add your own config,
            // in case of multiple developers/servers in different environments
            if (!empty(Base::$dev)) {
                /* @var string $devConfigFile app/config/myname.php */
                $devConfigFile = _DROOT.'/app/config/'.Base::$dev.'.php';
                if (is_file($devConfigFile) && is_readable($devConfigFile)) {
                    $devConfig = include_once $devConfigFile;
                    if (is_array($devConfig)) {
                        $config = array_replace_recursive($config, $devConfig);
                    }
                }
            }

            // injecting first config into DI/Pimple
            Base::$c = $config;

            Base::$cfg = $config['settings'];

        } catch (\Exception $e) {
            // Base::discard is not ready here. so using php error
            trigger_error('Unable to load config! Can not continue.', E_CORE_ERROR);
        }

    }


    /**
     * Module loader / Manager Loads modules that has register method defined
     *
     * @return null
     */
    final public static function setupModules()
    {

        $mods = Base::$cfg['modules'];

        if (is_array($mods) && count($mods) > 0) {
            // modules
            foreach ($mods as $m) {

                $mp = Base::MODULE_NS.ucfirst($m);

                if (is_callable([$mp, 'register'])) {

                    Base::$moduleRegistry[$m] = (object) [
                        'state' => null,
                        'methods' => get_class_methods($mp),
                        'instance' => false
                    ];

                    try {

                        // construct module
                        $nmi = new $mp(Base::$c, Base::$cfg);
                        Base::$moduleRegistry[$m]->instance = $nmi;

                        $r = Base::$moduleRegistry[$m]->instance->requires ?? [];
                        $missing = array_diff($r, Base::$modules);

                        if (($key = array_search('session', $missing)) !== false) {
                            session_start();
                            Base::$modules[] = 'session';
                            unset($missing[$key]);
                        }

                        if (count(array_diff($missing, $mods)) > 0) {
                            $em = $m.' modules requires: '.implode(',', $missing);
                            throw new \ErrorException($em);
                        }

                        $regs = Base::$moduleRegistry[$m]->instance->register();

                        Base::$moduleRegistry[$m]->state = $regs;

                    } catch (\ErrorException $e) {
                        $em = 'Unable to register module ('.$m.')';
                        $em.= 'via '.$mp.' '.$e->getMessage();
                        Base::log($em);
                    }
                } else {
                    $em = 'Module is missing or wrong ('.$m.') via '.$mp;
                    Base::log($em);
                }
            }
        }

    }





    /**
     * DB registration
     * Manages db connections
     * also sets up fs db via sqlite
     *
     * @return null
     */
    final public static function registerDB()
    {
        // ignore if none provided
        if (Base::$cfg['db'] ?? false) {

            if ($def = Base::$cfg['db']['default'] ?? false) {

                $type = 'App\Base::dba'.ucfirst($def['type']);
                if (is_callable($type)) {
                    Base::$c['db'] = call_user_func_array($type, [$def]);
                    // default key db
                    Base::$modules[] = 'db';
                }

            }
            // remove db config
            unset(Base::$cfg['db']['default']);

            // Redbean models
            define('REDBEAN_MODEL_PREFIX', '\\App\\Models\\');

            // additional databases
            if (count(Base::$cfg['db']) > 0) {

                foreach (Base::$cfg['db'] as $k => $cfg) {
                    $type = 'App\Base::dba'.ucfirst($cfg['type']);
                    if (is_callable($type)) {
                        call_user_func_array($type, [$cfg, $k]);
                        // register with key
                        Base::$modules[] = $k;
                    }
                    unset(Base::$cfg['db'][$k]);
                }

            }

            if (Base::$cfg['debugMode']) {
                //$pdo = R::getDatabaseAdapter()->getDatabase()->getPDO();
                //$pdo = new TraceablePDO($pdo);
                //Base::$debugbar->addCollector(new PDOCollector($pdo));
            }

        }

    }


    /**
     * Setup & Register Twig
     *
     * @return null
     */
    final public static function registerTwig()
    {

        // Register Twig View helper
        Base::$c['view'] = function ($c) {

            /* @var $c \Slim\Container */
            $twigConf = (object) $c->get('settings')['view'];

            $twig = new Twig($twigConf->templatePath, $twigConf->twig);

            $twig->addExtension(new \Twig_Extensions_Extension_I18n());

            //$profile = new \Twig_Profiler_Profile();
            //$twig->addExtension(new \Twig_Extension_Profiler($profile));

            // Instantiate and add Slim specific extension
            $ext = new TwigExtension($c['router'], $c['request']->getUri());

            $twig->addExtension($ext);

            if (Base::$cfg['debugMode'] ?? false) {

                $twig->addExtension(new \Twig_Extension_Debug());

            }

            array_push(Base::$modules, 'templates', 'twig');

            return $twig;
        };

    }


    /**
     * Setup & register Debugger which is Tracy now. sexy!
     *
     * @return null
     */
    final public static function registerDebugger()
    {

        Debugger::enable(Debugger::DEVELOPMENT, _DROOT.'/tmp/logs');

        Debugger::$showLocation = Dumper::LOCATION_SOURCE;

        Debugger::$productionMode = false;

        Debugger::$maxDepth = 2;

        Debugger::$maxLen = 50;

        Debugger::timer('Debugger loaded');

        Debugger::barDump('Debugger log!');

        Base::$c['tracy'] = true;

    }



    /**
     * Base Hive setter
     *
     * @param string $key Hive key
     * @param string $val Hive value
     *
     * @return mixed
     */
    public static function set($key, $val)
    {
        // check if it's an array or stdClass.
        if (strpos($key, Tools::ARR_PATH_SEP) !== false) {
            Tools::setByPath(Base::$hiveData, $key, $val);
        } else {
            Base::$hiveData[$key] = $val;
        }
        return $val;
    }


    /**
     * Base Hive getter
     *
     * @param string $key     Hive key
     * @param null   $default Default value
     *
     * @return null
     */
    public static function get($key, $default = null)
    {
        if (strpos($key, Tools::ARR_PATH_SEP) !== false) {
            $val = Tools::getByPath(Base::$hiveData, $key);
        } else {
            if (!empty(Base::$hiveData[$key])) {
                $val = Base::$hiveData[$key];
            } else {
                // fall back
                $val = $default;
            }
        }
        return $val;
    }


    /**
     * Response JSON
     *
     * @param mixed $data Data to serialize
     *
     * @return null
     */
    public static function json($data)
    {
        /* @var \Slim\Http\Response $r */
        $r = Base::$response->withHeader('Content-Type', 'application/json');

        $r->getBody()->write(json_encode($data));

        Base::respond($r);

    }


    /**
     * Redirect Response
     *
     * @param string $route Target route name
     * @param array  $args  Args if needed
     * @param int    $code  HTTP code
     *
     * @return null
     */
    public static function redirect($route, $args = [], $code = 301)
    {

        $url = Base::pathFor($route, $args);

        $r = Base::$c['response']->withStatus($code)->withHeader('Location', $url);

        Base::respond($r);

    }


    /**
     * State logging messages
     *
     * @param null $msg State message
     *
     * @return null
     */
    public static function stateLog($msg = null)
    {

        if (Base::$cfg['debugMode'] ?? false) {

            $msg = $msg ?? 'No state message';

            Base::$timing[] = Debugger::timer($msg);

        }

    }


    /**
     * Generic logger fn
     *
     * @param mixed $log Log subject
     *
     * @return null
     */
    public static function log($log)
    {
        Debugger::log($log);
    }




    /**
     * Generic web console logger fn,
     *
     * @param mixed $log Log subject
     *
     * @return null
     */
    public static function clog($log)
    {
        Debugger::fireLog($log);
    }


    /**
     * Generic cookie setter.
     *
     * @param string $key Cookie Key
     * @param mixed  $val Value
     *
     * @return null
     */
    public static function setCookie($key, $val)
    {
        // @Todo: fix this when slim3 cookies starts to work again.
        //Base::$c->get('cookies')->set($key, [
        //    $key => $val,
        //    'expires' => '7 days'
        //]);

        $h = $key.'='. $val.'; Max-Age=43600; path=/; httponly';
        $r = Base::$response->withHeader('Set-Cookie', $h);
        Base::$response = $r;
    }




    /**
     * Silent Logger, simply redirects all Exceptions & Errors to logger app.log.
     *
     * @param int    $errno Error no
     * @param string $msg   Error message
     * @param string $file  File path
     * @param int    $line  Line
     * @param mixed  $ctx   Context?
     *
     * @return bool
     */
    public static function silentLogger($errno, $msg, $file, $line, $ctx)
    {
        $errData = [
            'errno'   => $errno,
            'error'   => $msg,
            'file'    => $file.':'.$line,
            'context' => $ctx,
            'stack'   => debug_backtrace()
        ];

        return true;
    }



    /**
     * App error Handler setter
     * Sets the default errorHandler of php
     *
     * @param int    $errno Error no
     * @param string $msg   Error message
     * @param string $file  File path
     * @param int    $line  Line
     * @param mixed  $ctx   Context?
     *
     * @return bool
     */
    final public static function errorHandler($errno, $msg, $file, $line, $ctx)
    {

        $errData = [
            'errno'   => $errno,
            'error'   => $msg,
            'file'    => $file.':'.$line,
            'context' => $ctx,
            'stack'   => debug_backtrace()
        ];

        Base::log($errData);

        // @TODO: Refactor here

        return true;
    }



    /**
     * Path for
     *
     * @param string $routeName Route name
     * @param array  $args      Route arguments
     *
     * @return string
     */
    public static function pathFor($routeName, $args = [])
    {
        return Base::$c->get('router')->pathFor($routeName, $args);
    }



    /**
     * Fixed Render Method.
     * DO NOT PLAY with this unless you know what you are doing!
     *
     * @param string $template View name
     * @param array  $data     Data array
     *
     * @return null
     */
    final public static function render($template, $data = [])
    {

        Base::stateLog('Rendering '.$template);
        // rendering defaults.

        if (empty(Base::$hiveData['project'])) {
            Base::$hiveData['project'] = Base::$c['project'];
        }

        // Base::json(debug_backtrace()); exit;

        //foreach (debug_backtrace() as $k => $callItem) {
        //    if ($callItem['class'] ?? false) {
        //        // if it's a module or an action
        //
        //        $ta = substr($callItem['class'], 0, 10) == 'App\Action';
        //        if ($callItem['class'] ?? false && $ta) {
        //            $calledAction = $callItem;
        //        }
        //
        //        $tm = substr($callItem['class'], 0, 11) == 'App\Modules';
        //        if ($callItem['class'] ?? false && $tm) {
        //            $calledModule = $callItem;
        //        }
        //
        //    }
        //}

        /*
        if ($calledModule ?? false) {
            // method of module, most likely a route fn
            $method = strtolower($calledModule['function']);
            // Class name of module, actually module name
            $classPath = $calledModule['class'];
            // Get rid of NS
            $name = str_replace('App\\', '', $classPath);
            // remove if it's already in Modules
            $name = str_replace('Modules\\', '', $name);
            // requirements of module
            $requires = $calledModule['object']->requires ?? null;

            /* @var \Slim\Http\Request $lastReq *//*
            $lastReq = $calledModule['args'][0];


            if ($lastReq instanceof Request) {
                $route = $lastReq->getAttribute('route')->getName();
            }

            $args = $lastReq = $calledModule['args'][2];

            $m = compact('name', 'method', 'route', 'classPath', 'requires', 'args');

            Base::$hiveData['module'] = $m;

            // Attach Module properties, if there are any.
            // // NOTE: this can override the props above.
            foreach ($calledModule['object']->module as $k => $v) {
                Base::$hiveData['module'][$k] = $v;
            }

        } */

        // Avatar service: Gravatar
        if (Base::get('login')) {
            Base::$hiveData['login'] = Session::get('login');
        }

        /* @var $twig \Twig_Environment */
        $twig = Base::$c->get('view')->getEnvironment();

        foreach (Base::$hiveData as $k => $v) {
            $twig->addGlobal($k, $v);
        }

        /* @var \Slim\Http\Response $lastResp */
        //$lastResp = $called['args'][1];

        // Fix Response from Abstract Response.
        //if ($lastResp instanceof Response  ?? false) {
        //    Base::$response = $lastResp;
        //}

        return Base::$c->get('view')->render(Base::$response, $template, $data);

    }


    /**
     * Locale loader. relies on gettext
     * po/mo files has to be placed in /i18n directory
     *
     * @return mixed
     */
    public static function setLocale()
    {
        // sets the locale by browser language,
        // may ignore language variant, before switching to fallback

        /**
         * Inner fn to process
         *
         * @param string $locale Locale name
         *
         * @return bool
         */
        function setLang($locale)
        {
            // Do not assign LC_ALL since the number notation varies between them.
            // putenv('LC_ALL='.$locale.'.UTF-8');
            putenv('LC_MESSAGES='.$locale.'.UTF-8');
            setlocale(LC_MESSAGES, $locale.'.UTF-8');

            bindtextdomain('app', _DROOT.'/i18n/');
            textdomain('app');
            bind_textdomain_codeset('app', 'UTF-8');

            return true;
        }

        /* @var \Psr\Http\Message\ServerRequestInterface $r */
        $r = Base::$c->get('request');

        $il = [];
        $cfg = Base::$c['settings']['locale'];

        $uln = explode(',', ($r->getServerParams()['HTTP_ACCEPT_LANGUAGE']??'en'));

        foreach ($cfg['available'] as $l) {
            $il[strtolower(substr($l, 0, 2))] = $l;
        }

        // user preference,
        // just create a hostonly cookie with JS. then reload.
        if ($upl = $r->getCookieParams()[$cfg['switch']] ?? false) {
            if (in_array($upl, array_keys($il))) {
                return setLang($il[$upl]);
            }
        }

        foreach ($uln as $ln) {
            $ln = trim(str_replace('-', '_', $ln));
            if (strpos($ln, ';')) {
                $ln = strstr($ln, ';', true);
            }
            // now lang should be 2 letters or 4 with variant
            if (in_array($ln, $il)) {
                return setLang($ln);
            }
            if (in_array($ln, array_keys($il))) {
                return setLang($il[$ln]);
            }
        }

        // fallback locale.
        return setLang($cfg['fallback']);

    }








    /**
     * Outputs a file.
     *
     * @param string $filePath File path
     *
     * @return null
     */
    public static function sendFile($filePath)
    {
        /* @var Response $r */
        $r = Base::$c['response'];

        $r->withHeader('Content-Type', Tools::getMimeType($filePath));
        $r->getBody()->write(file_get_contents($filePath));

        Base::respond($r);
    }


    /**
     * Generic Dumper
     *
     * @param mixed $data Var to dump
     *
     * @return null
     */
    public static function dump($data)
    {
        /* @var \Slim\Http\Response $r */
        $r = Base::$c['response']->withHeader('X-Mod-Run', 'Base::dump');

        $call = debug_backtrace(null, 1)[0];

        $last = debug_backtrace(null, 2)[1];

        Debugger::barDump($_SERVER, 'Server Vars');

        Debugger::barDump($last, 'Previous Call');

        if (function_exists('dump')) {
            ob_start();  echo dump($data);  $content = ob_get_clean();
        } else {
            ob_start(); var_dump($data); $content = ob_get_clean();
        }

        $view = Base::$c->get('view');

        $view->render($r, 'modules/dump.twig', compact('content', 'call', 'last'));

        Base::respond($r);
    }


    /**
     * PostApp state
     *
     * @return null
     */
    final public static function postApp()
    {

        // Only debugMode
        if (Base::$cfg['debugMode'] ?? false) {

            Base::$app->add(new Middlewares\Debugger(Base::$c));

            if (Base::$cfg['handleExceptions'] ?? false) {
                // custom error handler
                Base::$c['errorHandler'] = function ($c) {

                    return function ($request, $response, $exception) use ($c) {
                        $handler = new ErrorHandler($c);
                        return $handler->display($request, $response, $exception);
                    };
                };
            }

        }

        // postApp Hooks


        // Gettext can not read .twig files
        // so we have to convert them to PHP files
        // dumping all slugs in twig files
        // into temp directory > _DROOT/tmp/i18n-cache/

        $dumper = '\App\Base::dumpGettextStr';
        Base::$app->get(Base::$cfg['locale']['dumpPath'], $dumper);

    }



    /**
     * Returns available Routes list for logged in User
     *
     * @return array
     */
    public static function getRouteList()
    {
        $list = [];

        $routes = Base::$c->get('router')->getRoutes();

        foreach ($routes as $k => $route) {
            /* @var \Slim\Route $route */

            $list[$k] = [
                'name' => $route->getName(),
                'module' => $route->getCallable(),
                'path' => $route->getPattern(),
                'method' => $route->getMethods(),
                'args' => $route->getArguments()
            ];
        }

        return $list;
    }


    /**
     * Gettext slug dumper
     *
     * @return array
     */
    public static function dumpGettextStr()
    {
        $list = [];

        $uri = Base::$request->getUri();

        $tplDir = _DROOT.'/views';

        $loader = new \Twig_Loader_Filesystem($tplDir);
        // force auto-reload to always have the latest version of the template
        $cfg = [
            'cache' => _DROOT.'/tmp/i18n-cache/',
            'auto_reload' => true
        ];

        $twig = new \Twig_Environment($loader, $cfg);

        $twig->addExtension(new \Twig_Extension_Debug());

        $twig->addExtension(new TwigExtension(Base::$c['router'], $uri));

        $twig->addExtension(new \Twig_Extensions_Extension_I18n());

        $lf = \RecursiveIteratorIterator::LEAVES_ONLY;

        $it = new \RecursiveDirectoryIterator($tplDir);

        $lIt = new \RecursiveIteratorIterator($it, $lf);

        foreach ($lIt as $file) {
            if ($file->isFile()) {
                $list[] = str_replace($tplDir.'/', '', $file);
                $twig->loadTemplate(str_replace($tplDir.'/', '', $file));
            }
        }

        Base::json(['msg' => 'OK', 'list' => $list]);

    }


    /**
     * Generic exception thrower
     *
     * @param \Exception|\ErrorException|\Error $e Error or Exception
     *
     * @return bool
     */
    public static function discard($e)
    {
        // @TODO: Refactor this!

        if (Base::$cfg['debugMode'] ?? false) {

            $last = debug_backtrace()[1];

            $ln = 'Exception in '.$last['file'].':'.$last['line'];
            $ln.= ' - '.$last['class'].$last['type'].$last['function'];

            Base::log($ln);

        } // ? not sure if this is correct

        return false;
    }


    /**
     * Sends PSR-7 Response to browser & exits.
     * Cuts through Slim3 response logic. so use it wisely.
     * Ripped off from Slim3
     *
     * @param \Slim\Http\Response $response Response
     *
     * @return null
     */
    final public static function respond(Response $response)
    {
        // Send response
        if (!headers_sent()) {
            // Status
            header(
                sprintf(
                    'HTTP/%s %s %s',
                    $response->getProtocolVersion(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                )
            );

            // Headers
            foreach ($response->getHeaders() as $name => $values) {
                foreach ($values as $value) {
                    header(sprintf('%s: %s', $name, $value), false);
                }
            }
        }


        $body = $response->getBody();

        if ($body->isSeekable()) {
            $body->rewind();
        }

        $chunkSize = 1024*32;
        $contentLength = $response->getHeaderLine('Content-Length');

        if (!$contentLength) {
            $contentLength = $body->getSize();
        }

        $totalChunks = ceil($contentLength / $chunkSize);
        $lastChunkSize = $contentLength % $chunkSize;
        $currentChunk = 0;

        while (!$body->eof() && $currentChunk < $totalChunks) {

            if (++$currentChunk == $totalChunks && $lastChunkSize > 0) {
                $chunkSize = $lastChunkSize;
            }

            echo $body->read($chunkSize);

            if (connection_status() != CONNECTION_NORMAL) {
                break;
            }
        }
        exit;
    }

    /**
     * MariaDB/MySQL connection
     *
     * @param array  $cfg Config
     * @param string $dn  literal name
     *
     * @throws \RedBeanPHP\RedException
     *
     * @return null
     */
    public static function dbaMysql($cfg, $dn = 'default')
    {
        if ($dn == 'default') {

            R::setup(
                'mysql:host='.$cfg['host'].';dbname='.$cfg['db'],
                $cfg['user'],
                $cfg['pass']
            );

        } else {

            R::addDatabase(
                $dn,
                'mysql:host='.$cfg['host'].';dbname='.$cfg['db'],
                $cfg['user'],
                $cfg['pass'],
                $cfg['freeze']
            );

        }
    }

    /**
     * SQLite connection
     *
     * @param array  $cfg Config
     * @param string $dn  Literal name
     *
     * @throws \RedBeanPHP\RedException
     *
     * @return null
     */
    public static function dbaSqlite($cfg, $dn = 'default')
    {
        if ($dn == 'default') {

            R::setup('sqlite:'.$cfg['path'], $cfg['user'], $cfg['pass']);

        } else {

            R::addDatabase(
                $dn,
                'sqlite:'.$cfg['path'],
                $cfg['user'],
                $cfg['pass'],
                $cfg['freeze']
            );

        }
    }


    /**
     * PostgreSQL connection
     *
     * @param array  $cfg Config arr
     * @param string $dn  Literal name
     *
     * @throws \RedBeanPHP\RedException
     *
     * @return null
     */
    public static function dbaPgsql($cfg, $dn = 'default')
    {
        if ($dn == 'default') {

            R::setup(
                'pgsql:host='.$cfg['host'].';dbname='.$cfg['db'],
                $cfg['user'],
                $cfg['pass']
            );

        } else {

            R::addDatabase(
                $dn,
                'pgsql:host='.$cfg['host'].';dbname='.$cfg['db'],
                $cfg['user'],
                $cfg['pass'],
                $cfg['freeze']
            );

        }
    }
}
