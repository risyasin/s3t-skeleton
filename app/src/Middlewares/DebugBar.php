<?php

/**
 * Created by PhpStorm.
 * User: yas
 * Date: 14/12/15
 * Time: 23:22
 */

namespace App\Middlewares;


use \GuzzleHttp\Psr7\LazyOpenStream;

/**
 * Class DebugBar
 * @package App\Middlewares
 */
class DebugBar
{

    const resourcePath = '/vendor/maximebf/debugbar/src/DebugBar/Resources';

    public $container;

    /* @var $logger \Monolog\Logger */
    public $logger;

    /* @var $debugbar \DebugBar\StandardDebugBar */
    public $debugbar;


    /**
     * DebugBar Middleware constructor.
     * @param $container
     */
    public function __construct($container)
    {
        /* @var $container \Slim\Container */
        $this->container = $container;

        $this->logger = $this->container->get('logger');

        $this->debugbar = $this->container->get('debugbar');

    }



    /**
     * Example middleware invokable class
     *
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke($request, $response, $next)
    {

        $pnc = strlen($this::resourcePath);
        $path = $request->getUri()->getPath();

        if (substr($path, 0, $pnc) == $this::resourcePath){

            $newStream = new LazyOpenStream(_DROOT.$path, 'r');

            return $response->withHeader('Content-Type', $this->getMimeType(_DROOT.$path))->withBody($newStream);

        }

        //$response->getBody()->write('BEFORE');
        $response = $next($request, $response);


        $appendHead = $this->debugbar->getJavascriptRenderer()->renderHead();
        $response = $this->replaceContent($response, '</head>', $appendHead.'</head>');

        $appendBody = $this->debugbar->getJavascriptRenderer()->render();
        $response = $this->replaceContent($response, '</body>', $appendBody.'</body>');

        return $response;
    }


    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param $find
     * @param $replace
     * @return \Psr\Http\Message\ResponseInterface
     */
    private function replaceContent($response, $find, $replace)
    {

        $str = (string) $response->getBody();
        $response->getBody()->rewind();
        $response->getBody()->write(str_replace($find, $replace, $str));

        return $response;
    }


    private function getMimeType($filePath)
    {
        $extMap = [
            'js' => 'text/javascript',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'woff' => 'application/font-woff',
            'woff2' => 'application/font-woff2'
        ];


        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        // $this->logger->info('ext: '.$ext.' - '.$filePath);

        if (array_key_exists($ext, $extMap)){

            return $extMap[$ext];

        } else {

            $type = 'text/html';

            $finfo = finfo_open(FILEINFO_MIME_TYPE);

            foreach (glob("*") as $filePath) {
                $type  = finfo_file($finfo, $filePath);
            }

            finfo_close($finfo);

            return $type;
        }

    }
}