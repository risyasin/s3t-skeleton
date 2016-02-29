<?php

/**
 * Created by PhpStorm.
 * User: yas
 * Date: 14/12/15
 * Time: 23:22
 */

namespace App\Middlewares;


use App\Base;
use Monolog\Handler\BrowserConsoleHandler;

/**
 * Class Monolog
 * Adds BrowserConsoleHandler handler to Logger
 *
 * @package Ra\Sc\Middlewares
 */
class Monolog
{

    /**
     * DebugBar Middleware constructor.
     * @param $container
     */
    public function __construct($container)
    {
        /* @var $container \Slim\Container */
    }



    /**
     * Example middleware invokable class
     *
     * @param \Slim\Http\Request $request  PSR7 request
     * @param \Slim\Http\Response $response PSR7 response
     * @param callable $next Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {

        $logger = Base::$logger; $scriptSnippet = '';

        $response = $next($request, $response);

        $handlers = $logger->getHandlers();

        foreach($handlers as $handler){
            if($handler instanceof BrowserConsoleHandler){
                // capture Broswerhandler's output
                ob_start();
                $handler->send();
                $scriptSnippet .= ob_get_clean();
            }
        }

        // now attach it to response
        return $this->replaceContent($response, '</body>', $scriptSnippet.'</body>');
    }


    /**
     * @param \Slim\Http\Response $response PSR7 response
     * @param $find
     * @param $replace
     * @return \Slim\Http\Response
     */
    private function replaceContent($response, $find, $replace)
    {

        $str = (string) $response->getBody();
        $response->getBody()->rewind();
        $response->getBody()->write(str_replace($find, $replace, $str));

        return $response;
    }


}