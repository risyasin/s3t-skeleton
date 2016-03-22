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
namespace App\Utils;

use Exception;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Body;

/**
 * Class ErrorHandler
 * Shamelessly rip off of it's native.
 * to replace Slim's native ErrorHandler.
 * Also add's debugbar to error page
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */

class ErrorHandler
{

    /* @var $container \Slim\Container  */
    protected $container;

    /* @var $this->dbjr \DebugBar\JavascriptRenderer */
    protected $dbjr;

    protected $details;

    /**
     * Known handled content types
     *
     * @var array
     */
    protected $knownContentTypes = [
        'application/json',
        'application/xml',
        'text/xml',
        'text/html',
    ];

    /**
     * ErrorHandler constructor.
     *
     * @param \Slim\Container $container DIC
     */
    public function __construct($container)
    {

        $this->dbjr = $container->get('debugbar')->getJavascriptRenderer();

        $this->details = (bool) $container->get('settings')['displayErrorDetails'];

    }

    /**
     * Display error
     *
     * @param Request    $request   The most recent Request object
     * @param Response   $response  The most recent Response object
     * @param \Exception $exception The caught Exception object
     *
     * @return Response
     */
    public function display(Request $request, Response $response, $exception)
    {

        $output = 'Unknown';

        $contentType = $this->_determineContentType($request);

        switch ($contentType) {
        case 'application/json':
            $output = $this->renderJsonErrorMessage($exception);
            break;

        case 'text/xml':
        case 'application/xml':
            $output = $this->renderXmlErrorMessage($exception);
            break;

        case 'text/html':
            $output = $this->renderHtmlErrorMessage($exception);
            break;
        }

        $body = new Body(fopen('php://temp', 'r+'));
        $body->write($output);

        return $response
            ->withStatus(500)
            ->withHeader('Content-type', $contentType)
            ->withBody($body);
    }

    /**
     * Render HTML error page
     *
     * @param \Exception $exception Exception
     *
     * @return string
     */
    protected function renderHtmlErrorMessage($exception)
    {

        $title = 'Application Error';

        if ($this->details) {
            $html = '<p>The application could not run '.
                'because of the following error:</p>';
            $html .= '<h2>Details</h2>';
            $html .= $this->renderHtmlException($exception);

            while ($exception = $exception->getPrevious()) {
                $html .= '<h2>Previous exception</h2>';
                $html .= $this->renderHtmlException($exception);
            }
        } else {
            $html = '<p>A website error has occurred. '.
                'Sorry for the temporary inconvenience.</p>';
        }

        $appendHead = '';
        $appendBody = '';

        $style = 'body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,'.
            'Verdana,sans-serif;}h1{margin:0;font-size:24px;font-weight:normal;'.
            'line-height:30px;}strong{display:inline-block;width:65px;}';

        return '<html><head><meta http-equiv="Content-Type" content="text/html; '.
        'charset=utf-8"><title>'.$title.'</title><style>'.$style.'</style>'.
        $appendHead.'</head><body><h1>'.$title.'</h1>'.$html.$appendBody.
        '</body></html>';

    }

    /**
     * Render exception as HTML.
     *
     * @param \Exception $exception Error Exception
     *
     * @return string
     */
    protected function renderHtmlException($exception)
    {
        $html = '<div><strong>Type:</strong> '.get_class($exception).'</div>';

        if (($code = $exception->getCode())) {
            $html .= '<div><strong>Code:</strong> '.$code.'</div>';
        }

        if (($message = $exception->getMessage())) {
            $html .= '<div><strong>Message:</strong>'.
                htmlentities($message).'</div>';
        }

        if (($file = $exception->getFile())) {
            $html .= '<div><strong>File:</strong> '.$file.'</div>';
        }

        if (($line = $exception->getLine())) {
            $html .= '<div><strong>Line:</strong> '.$line.'</div>';
        }

        if (($trace = $exception->getTraceAsString())) {
            $html .= '<h2>Trace</h2>';
            $html .= '<pre>'.htmlentities($trace).'</pre>';
        }

        return $html;
    }

    /**
     * Render JSON error
     *
     * @param \Exception $exception Exception
     *
     * @return string
     */
    protected function renderJsonErrorMessage($exception)
    {
        $error = [
            'message' => 'Slim Application Error',
        ];

        if ($this->details) {
            $error['exception'] = [];

            do {
                $error['exception'][] = [
                    'type' => get_class($exception),
                    'code' => $exception->getCode(),
                    'message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'trace' => explode("\n", $exception->getTraceAsString()),
                ];
            } while ($exception = $exception->getPrevious());
        }

        return json_encode($error, JSON_PRETTY_PRINT);
    }

    /**
     * Render XML error
     *
     * @param \Exception $exception Exception
     *
     * @return string
     */
    protected function renderXmlErrorMessage($exception)
    {
        $xml = "<error>\n  <message>Application Error</message>\n";
        if ($this->details) {
            do {
                $xml .= "  <exception>\n";
                $xml .= "    <type>" . get_class($exception) . "</type>\n";
                $xml .= "    <code>" . $exception->getCode() . "</code>\n";
                $xml .= "    <message>" .
                    $this->_createCdataSection($exception->getMessage()).
                    "</message>\n";
                $xml .= "    <file>" . $exception->getFile() . "</file>\n";
                $xml .= "    <line>" . $exception->getLine() . "</line>\n";
                $xml .= "    <trace>" .
                    $this->_createCdataSection($exception->getTraceAsString()).
                    "</trace>\n";
                $xml .= "  </exception>\n";
            } while ($exception = $exception->getPrevious());
        }
        $xml .= "</error>";

        return $xml;
    }

    /**
     * Returns a CDATA section with the given content.
     *
     * @param string $content CDATA content
     *
     * @return string
     */
    private function _createCdataSection($content)
    {
        return sprintf(
            '<![CDATA[%s]]>',
            str_replace(']]>', ']]]]><![CDATA[>', $content)
        );
    }

    /**
     * Determine which content type we know about is wanted using Accept header
     *
     * @param Request $request Request
     *
     * @return string
     */
    private function _determineContentType(Request $request)
    {
        $acceptHeader = $request->getHeaderLine('Accept');
        $selectedContentTypes = array_intersect(
            explode(',', $acceptHeader), $this->knownContentTypes
        );

        if (count($selectedContentTypes)) {
            return $selectedContentTypes[0];
        }

        return 'text/html';
    }
}