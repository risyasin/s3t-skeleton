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

namespace App\Middlewares;

use App\Base;
use Slim\Http\Request;
use Slim\Http\Response;
use RedBeanPHP\R as R;

/**
 * Class Debugger
 * Tracy Debugger Output
 *
 * @category Base
 * @package  App\Middlewares
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Debugger
{

    /**
     * Monolog Middleware constructor.
     *
     * @param \Slim\Container $container Slim Container
     */
    public function __construct($container)
    {
        /* @var $container \Slim\Container */
    }



    /**
     * Example middleware invokable class
     *
     * @param Request  $request  PSR7 request
     * @param Response $response PSR7 response
     * @param callable $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $tracy = '<!--// TRACY //-->';

        $response = $next($request, $response);


        ob_start();
        //OutputDebugger::enable();
        \Tracy\Debugger::getBar()->render();
        $tracy = ob_get_clean();

        // now attach it to response
        return $this->replaceContent($response, '</body>', $tracy.'</body>');
    }


    /**
     * Content replace
     *
     * @param Response $response PSR7 response
     * @param string   $find     Find string
     * @param string   $replace  Replace string
     *
     * @return Response
     */
    public function replaceContent(Response $response, $find, $replace)
    {

        $str = (string) $response->getBody();
        $response->getBody()->rewind();
        $response->getBody()->write(str_replace($find, $replace, $str));

        return $response;
    }


}