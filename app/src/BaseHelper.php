<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 21/12/15
 * Time: 01:03
 */

namespace App;

use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Monolog\Handler\StreamHandler;
use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\Bridge\Twig\TraceableTwigEnvironment;
use DebugBar\Bridge\Twig\TwigCollector;
//use DebugBar\DataCollector\ExceptionsCollector;
use DebugBar\DataCollector\PDO\TraceablePDO;
use DebugBar\DataCollector\PDO\PDOCollector;
use RedBeanPHP\R as R;


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
     */
    private static function setupConfig()
    {

        try {
            // Default config.
            $config = require _DROOT.'/app/config/default.php';

            // you can overwrite your setting by environment variable "APP_ENV".
            $envConfigFile = _DROOT.'/app/config/'.Base::$env.'.php';
            if (is_file($envConfigFile) && is_readable($envConfigFile)){
                $envConfig = require $envConfigFile;
                if (is_array($envConfig)){ $config = array_replace_recursive($config, $envConfig); }
            }


            // you can even add your own config,
            // in case of multiple developers/servers in different environments
            if (!empty(Base::$dev)) {
                /* @var String $devConfigFile app/config/myname.php */
                $devConfigFile = _DROOT.'/app/config/'.Base::$dev.'.php';
                if (is_file($devConfigFile) && is_readable($devConfigFile)){
                    $devConfig = require $devConfigFile;
                    if (is_array($devConfig)){ $config = array_replace_recursive($config, $devConfig); }
                }
            }

            // injecting first config into DI/Pimple
            Base::$c = $config;

            Base::$cfg = $config['settings'];

        } catch(\Exception $e) {
            // Base::discard is not ready here. so using php error
            trigger_error('Unable to load config! Can not continue.', E_CORE_ERROR);
        }

    }



    /**
     * Module loader / Manager
     * Loads modules that has register method defined
     *
     */
    public static function setupModules()
    {

        $mods = Base::$cfg['modules'];

        if (is_array($mods) && count($mods) > 0){
            // modules
            foreach($mods as $m) {

                $mp = Base::moduleNS.ucfirst($m);

                if (is_callable([$mp, 'register'])){

                    Base::$_mreg[$m] = (object) [
                        'state' => null,
                        'methods' => get_class_methods($mp),
                        'instance' => false
                    ];

                    try {

                        // construct module
                        Base::$_mreg[$m]->instance = new $mp(Base::$c, Base::$cfg);

                        $missing = array_diff(Base::$_mreg[$m]->instance->requires, Base::$modules);

                        if(($key = array_search('session', $missing)) !== false) {
                            session_start();
                            Base::$modules[] = 'session';
                            unset($missing[$key]);
                        }

                        if (count(array_diff($missing, $mods)) > 0){
                            throw new \ErrorException($m.' modules requires: '.implode(',', $missing));
                        }

                        Base::$_mreg[$m]->state = Base::$_mreg[$m]->instance->register();

                    } catch(\ErrorException $e) {
                        Base::$logger->error('Unable to register module ('.$m.') via '.$mp.' '.$e->getMessage());
                    }
                } else {
                    Base::$logger->error('Module is missing or wrong ('.$m.') via '.$mp);
                }
            }
        }

    }


    /**
     * Base Hive setter
     *
     * @param $key
     * @param $val
     * @return mixed
     */
    public static function set($key, $val)
    {

        Base::$_data[$key] = $val;

        return $val;

    }


    /**
     * Base Hive getter
     *
     * @param $key
     * @param null $default
     * @return null
     */
    public static function get($key, $default = null)
    {

        return Base::$_data[$key] ?? $default;

    }


    /**
     * Response as JSON
     *
     * @param $data
     * @return int
     */
    public static function json($data)
    {
        /* @var $r \Psr\Http\Message\ResponseInterface */
        $r = Base::$c['response']->withHeader('Content-Type', 'application/json');
        return $r->getBody()->write(json_encode($data));
    }


    /**
     * Response redirect
     *
     * @param $url
     * @param int $code
     * @return mixed
     */
    public static function redirect($url, $code = 301)
    {
        Base::$c->get('logger')->info('redirecting to '.$url);
        return Base::$c['response']->withStatus($code)->withHeader('Location', $url);

    }


    /**
     * State logging messages
     * @param null $msg
     */
    public static function stateLog($msg = null)
    {

        if (Base::$debugbar){
            /* @var \DebugBar\DataCollector\TimeDataCollector $tc */
            $tc = Base::$debugbar['time'];

            if (Base::$currentState > 0 &&
                $tc->hasStartedMeasure((string) Base::$currentState)) {
                $tc->stopMeasure((string) Base::$currentState);
            }

            ++Base::$currentState;

            $msg = $msg ?? 'State changed to '.Base::$currentState;

            $tc->startMeasure((string) Base::$currentState, $msg);

        }

    }



    /**
     * Generic logger fn, mutant method. overloads by environment
     * @param $log
     */
    public static function log($log)
    {
        if (Base::$debugbar){
            Base::$c->get('debugbar')['messages']->info($log);
        } else {
            Base::$c->get('logger')->info($log);
        }
    }


    /**
     * Generic Error logger fn, mutant method. overloads by environment
     * @param $log
     */
    public static function errorLog($log)
    {
        if (Base::$debugbar ?? false){
            Base::$debugbar['messages']->error($log);
        } else {
            Base::$c->get('logger')->error($log);
        }
    }


    /**
     * General cookie setter.
     * @Todo: fix this when slim3 cookies starts to work again.
     *
     * @param $key
     * @param $val
     */
    public static function setCookie($key, $val)
    {
        // @TODO: improve here!
        //Base::$c->get('cookies')->set($key, [
        //    $key => $val,
        //    'expires' => '7 days'
        //]);
        Base::$response = Base::$c['response']->withHeader('Set-Cookie', $key.'='. $val.'; Max-Age=43600; path=/; httponly');
    }


    /**
     * @param $pathName
     * @return mixed
     */
    public static function pathFor($pathName)
    {
        return Base::$c->get('router')->pathFor($pathName);
    }


    /**
     * View render Template engine fn.
     * renders a template with given data & globals in hive array
     *
     * @param $template
     * @param array $data
     */
    public static function render($template, $data = [])
    {

        // rendering defaults.

        if (Base::$_data['project'] ?? true){
            Base::$_data['project'] = Base::$c['project'];
        }

        $called = debug_backtrace()[1];

        Base::$_data['module'] = array(
            'method' => strtolower($called['function']),
            'name' => strtolower(str_replace('App\Modules\\', '', $called['class']))
        );

        // Avatar service: Gravatar
        if ($l = Base::get('login')){
            Base::$_data['avatar_url'] = 'https://gravatar.com/avatar/'.md5($l->mail);
        }

        /* @var $twig \Twig_Environment */
        $twig = Base::$c->get('view')->getEnvironment();

        foreach(Base::$_data as $k => $v) {
            $twig->addGlobal($k, $v);
        }

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
        // sets the locale by browser language, may ignore language variant, before switching to fallback

        function setLang($locale) {
            // Do not assign LC_ALL since the number notation varies between languages.
            // putenv('LC_ALL='.$locale.'.UTF-8');
            putenv('LC_MESSAGES='.$locale.'.UTF-8');
            setlocale(LC_MESSAGES, $locale.'.UTF-8');

            bindtextdomain('app', _DROOT.'/i18n/');
            textdomain('app');
            bind_textdomain_codeset('app', 'UTF-8');

            return true;
        }

        /* @var $r \Psr\Http\Message\ServerRequestInterface  */
        $r = Base::$c['request'];

        $il = [];
        $cfg = Base::$c['settings']['locale'];
        $uln = explode(',', ($r->getServerParams()['HTTP_ACCEPT_LANGUAGE']??'en'));

        foreach($cfg['available'] as $l) { $il[strtolower(substr($l, 0, 2))] = $l; }

        // user preference,
        // just create a hostonly cookie with JS. then reload.
        if ($upl = $r->getCookieParams()[$cfg['switch']] ?? false) {
           if (in_array($upl, array_keys($il))){
               return setLang($il[$upl]);
           }
        }

        foreach($uln as $ln) {
            $ln = trim(str_replace('-', '_', $ln));
            if (strpos($ln, ';')){ $ln = strstr($ln, ';', true); }
            // now lang should be 2 letters or 4 with variant
            if (in_array($ln, $il)){ return setLang($ln); }
            if (in_array($ln, array_keys($il))){ return setLang($il[$ln]); }
        }

        // fallback locale.
        return setLang($cfg['fallback']);

    }




    /**
     * DB registration
     * Manages db connections
     * also sets up fs db via sqlite
     *
     * @throws \DebugBar\DebugBarException
     */
    public static function registerDB()
    {
        // ignore if none provided
        if (Base::$cfg['db'] ?? false) {

            if ($def = Base::$cfg['db']['default'] ?? false){

                $type = 'App\Base::DB_'.$def['type'];
                if (is_callable($type)){
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
            if (count(Base::$cfg['db']) > 0){

                foreach(Base::$cfg['db'] as $k => $cfg) {
                    $type = 'App\Base::DB_'.$cfg['type'];
                    if (is_callable($type)){
                        call_user_func_array($type, [$cfg, $k]);
                        // register with key
                        Base::$modules[] = $k;
                    }
                    unset(Base::$cfg['db'][$k]);
                }

            }


            if (Base::$cfg['debugMode']){
                $pdo = new TraceablePDO(R::getDatabaseAdapter()->getDatabase()->getPDO());
                Base::$debugbar->addCollector(new PDOCollector($pdo));
            }

        }

    }




    /**
     *
     */
    public static function registerTwig()
    {

        // Register Twig View helper
        Base::$c['view'] = function ($c) {
            /* @var $c \Slim\Container */
            $twigConf = (object) $c->get('settings')['view'];

            $twig = new Twig($twigConf->templatePath, $twigConf->twig);

            $twig->addExtension(new \Twig_Extension_Debug());

            $twig->addExtension(new \Twig_Extensions_Extension_I18n());

            $profile = new \Twig_Profiler_Profile();

            $twig->addExtension(new \Twig_Extension_Profiler($profile));

            // $dumper = new \Twig_Profiler_Dumper_Html();

            $dumper = new \Twig_Profiler_Dumper_Text();
            // echo $dumper->dump($profile);

            Base::$logger->info($dumper->dump($profile));


            // Instantiate and add Slim specific extension
            $twig->addExtension(new TwigExtension($c['router'],$c['request']->getUri()));

            array_push(Base::$modules, 'templates', 'twig');

            if (Base::$debugbar ?? false) {
                $env = new TraceableTwigEnvironment($twig->getEnvironment());
                Base::$debugbar->addCollector(new TwigCollector($env));
            }

            return $twig;
        };

    }


    /**
     *
     */
    public static function registerMonolog()
    {

        $loggerConf = (object) Base::$cfg['monolog'];

        $logger = new Logger($loggerConf->name);

        $logger->pushProcessor(new UidProcessor());

        $logger->pushHandler(new StreamHandler($loggerConf->path, Logger::DEBUG));

        // monolog
        Base::$c['logger'] = function ($c) use($logger) { return $logger; };

        Base::$modules[] = 'monolog';

        Base::$logger = $logger;
    }


    /**
     *
     */
    public static function registerDebugBar()
    {

        $debugbar = new StandardDebugBar();

        $debugbar->addCollector(new ConfigCollector((array) Base::$cfg));

        // $debugbar->addCollector(new ExceptionsCollector());

        // DebugBar
        Base::$c['debugbar'] = function ($c) use($debugbar) {
            /* @var $c \Slim\Container */
            return $debugbar;
        };

        Base::$debugbar = $debugbar;

    }




    /**
     *
     */
    public static function postApp()
    {

        // Only debugMode
        if (Base::$cfg['debugMode']) {

            // /* @var $debugbar \DebugBar\DebugBar */
            // $debugbar = Base::$c->get('debugbar');
            // PhpDebugBarMiddleware is not working sadly.
            // $app->add(new PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware($container->get('debugbar')->getJavascriptRenderer()));
            // @Todo: check if PhpMiddleware\PhpDebugBar\PhpDebugBarMiddleware is working?
            Base::$app->add(new Middlewares\DebugBar(Base::$c));

            // custom error handler
            Base::$c['errorHandler'] = function ($c) {

                /**
                 * Custom Error Handler
                 * @param $request ServerRequestInterface
                 * @param $response ResponseInterface
                 * @param $exception \ErrorException
                 * @return \Slim\Http\Response
                 */
                return function ($request, $response, $exception) use ($c) {
                    $handler = new ErrorHandler($c);
                    return $handler->display($request, $response, $exception);
                };
            };

        }

        // postApp Hooks

        Base::$app->get(Base::$cfg['locale']['dumpPath'], function () {
            // Gettext can not read .twig files so we have to convert them to PHP files
            // dumping all slugs in twig files into temp directory > _DROOT/tmp/i18n-cache/
            return Base::json(['msg' => 'OK', 'list' => Base::dumpGettextStr()]);
        });


    }


    /**
     * @return array
     */
    public static function dumpGettextStr()
    {
        $list = [];
        $tplDir = _DROOT.'/views';
        $tmpDir = _DROOT.'/tmp/i18n-cache/';
        $loader = new \Twig_Loader_Filesystem($tplDir);
        // force auto-reload to always have the latest version of the template
        $twig = new \Twig_Environment($loader, array(
            'cache' => $tmpDir,
            'auto_reload' => true
        ));
        $twig->addExtension(new \Twig_Extension_Debug());

        $twig->addExtension(new TwigExtension(Base::$c['router'], Base::$c['request']->getUri()));

        $twig->addExtension(new \Twig_Extensions_Extension_I18n());

        $lf = \RecursiveIteratorIterator::LEAVES_ONLY;
        $dirIt = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tplDir), $lf);
        foreach ($dirIt as $file)
        {
            if ($file->isFile()) {
                $list[] = str_replace($tplDir.'/', '', $file);
                $twig->loadTemplate(str_replace($tplDir.'/', '', $file));
            }
        }

        return $list;

    }



    public static function silentLogger($errno, $errstr, $errfile, $errline, $errcontext)
    {
        $errData = [
            'errno' => $errno,
            'error' => $errstr,
            'file' => $errfile.':'.$errline,
            'context' => $errcontext,
            'stack' => debug_backtrace()
        ];

        Base::$c->get('logger')->error($errData);

        return true;
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
    {

        $errData = [
            'errno' => $errno,
            'error' => $errstr,
            'file' => $errfile.':'.$errline,
            'context' => $errcontext,
            'stack' => debug_backtrace()
        ];

        Base::errorLog($errData);

        $errorPage = new ErrorHandler(Base::$c);

        $errorPage->display(Base::$c->get('request'), Base::$c->get('response'), new \Error($errstr, $errno));

        return true;
    }


    public static function exceptionHandler($e)
    {

        $errorPage = new ErrorHandler(Base::$c);

        Base::respond($errorPage->display(Base::$c->get('request'), Base::$c->get('response'), $e));

    }


    /**
     * @param $e
     * @return bool
     */
    public static function discard($e)
    {
        /* @var Exception|\ErrorException $e */
        if (Base::$cfg['debugMode'] ?? false){
            $last = debug_backtrace()[1];
            Base::errorLog('Exception in '.$last['file'].':'.$last['line'].' - '.$last['class'].$last['type'].$last['function']);
            Base::$debugbar['exceptions']->addException($e);
        } else {
            $last = debug_backtrace()[1];
            Base::errorLog('Exception in '.$last['file'].':'.$last['line'].' - '.$last['class'].$last['type'].$last['function']);
            Base::errorLog($e->getMessage());
        }
        return false;
    }


    public static function respond(ResponseInterface $response)
    {
        // Send response
        if (!headers_sent()) {
            // Status
            header(sprintf(
                       'HTTP/%s %s %s',
                       $response->getProtocolVersion(),
                       $response->getStatusCode(),
                       $response->getReasonPhrase()
                   ));

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
        $chunkSize      = 1024*32;
        $contentLength  = $response->getHeaderLine('Content-Length');
        if (!$contentLength) {
            $contentLength = $body->getSize();
        }
        $totalChunks    = ceil($contentLength / $chunkSize);
        $lastChunkSize  = $contentLength % $chunkSize;
        $currentChunk   = 0;
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
     * @param $cfg
     * @param string $dn
     * @return bool|\RedBeanPHP\OODB
     * @throws \RedBeanPHP\RedException
     */
    public static function DB_mysql($cfg, $dn = 'default')
    {
        if ($dn == 'default'){
            R::setup('mysql:host='.$cfg['host'].';dbname='.$cfg['db'], $cfg['user'], $cfg['pass']);
            return R::getRedBean();
        } else {
            R::addDatabase($dn, 'mysql:host='.$cfg['host'].';dbname='.$cfg['db'], $cfg['user'], $cfg['pass'], $cfg['freeze']);
            return true;
        }
    }

    /**
     * SQLite connection
     *
     * @param $cfg
     * @param string $dn
     * @return bool|\RedBeanPHP\OODB
     * @throws \RedBeanPHP\RedException
     */
    public static function DB_sqlite($cfg, $dn = 'default')
    {
        if ($dn == 'default'){
            R::setup('sqlite:'.$cfg['path'], $cfg['user'], $cfg['pass']);
            return R::getRedBean();
        } else {
            R::addDatabase($dn, 'sqlite:'.$cfg['path'], $cfg['user'], $cfg['pass'], $cfg['freeze']);
            return true;
        }
    }


    /**
     * PostgreSQL connection
     *
     * @param $cfg
     * @param string $dn
     * @return bool|\RedBeanPHP\OODB
     * @throws \RedBeanPHP\RedException
     */
    public static function DB_pgsql($cfg, $dn = 'default')
    {
        if ($dn == 'default'){
            R::setup('pgsql:host='.$cfg['host'].';dbname='.$cfg['db'], $cfg['user'], $cfg['pass']);
            return R::getRedBean();
        } else {
            R::addDatabase($dn, 'pgsql:host='.$cfg['host'].';dbname='.$cfg['db'], $cfg['user'], $cfg['pass'], $cfg['freeze']);
            return true;
        }
    }



}