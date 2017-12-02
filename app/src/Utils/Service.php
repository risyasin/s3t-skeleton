<?php
/**
 * PHP version 7
 *
 * Created at 22/03/16 12:33 by yas
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3
 */

namespace App\Utils;

/**
 * Class Service
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3app
 */
class Service
{

    private static $_curl;

    private static $_dbg = false;

    private static $_lastOp;

    public static $options = [
        'header'         => true,
        'returntransfer' => true,
        'timeout'        => 30,
        'tcp_nodelay'    => true,
        'followlocation'    => true
    ];


    /**
     * Curl initializing
     *
     * @return null
     */
    public static function initCurl()
    {
        if (!self::$_curl) {
            self::$_curl = curl_init();
        }
    }


    /**
     * Set curl options
     *
     * @param string $opt Option name
     * @param mixed  $val Value
     *
     * @return null
     */
    public static function addOption($opt, $val)
    {

        $opt = ltrim(strtolower($opt), 'CURLOPT_');

        self::$options[$opt] = $val;

    }


    /**
     * GET Request
     *
     * @param string $url    URl to get
     * @param array  $params Parameters
     * @param array  $opts   Curl options
     *
     * @return object
     * @throws \Exception
     */
    public static function get($url, Array $params = [], Array $opts = [])
    {

        self::addOption('customrequest', 'GET');

        $url = sprintf("%s?%s", ltrim($url, "/"), http_build_query($params));

        self::addOption('url', $url);

        foreach ($opts as $k => $v) {
            self::addOption($k, $v);
        }

        return self::_getResult();
    }


    /**
     * POST Request
     *
     * @param string $url  URL
     * @param array  $data Payload as array
     * @param array  $opts Curl opts
     *
     * @return object
     * @throws \Exception
     */
    public static function post($url, Array $data = [], Array $opts = [])
    {
        self::addOption('url', $url);

        foreach ($opts as $k => $v) {
            self::addOption($k, $v);
        }

        self::addOption('post', true);

        if (!empty($opts['json']) && $opts['json'] == true) {

            self::addOption(
                'httpheader',
                [ 'Accept: application/json', 'Content-Type: application/json' ]
            );

            self::addOption('postfields', json_encode($data));

            // remove unnecessary header
            unset($opts['json']);

        } else {

            self::addOption('postfields', http_build_query($data));

        }

        return self::_getResult();

    }


    /**
     * PUT Request
     *
     * @param string $url  URL
     * @param array  $data Payload as array
     * @param array  $opts Curl opts
     *
     * @return null
     */
    public static function put($url, $data = [], Array $opts = [])
    {

        self::addOption('url', $url);

        foreach ($opts as $k => $v) {
            self::addOption($k, $v);
        }

        self::addOption('put', true);

        if (!is_array($data)) {
            // @TODO: file handler

        } elseif (!empty($opts['json']) && $opts['json'] == true) {

            self::addOption(
                'httpheader',
                ['Accept: application/json', 'Content-Type: application/json']
            );

            self::addOption('postfields', json_encode($data));

        } else {

            // switch to binary transfer mode!
            self::addOption('binarytransfer', true);

            $fp = fopen('php://temp/maxmemory:256000', 'w');

            if (!$fp) {
                trigger_error('Could not open PHP temp handler data', E_USER_ERROR);
            }

            fwrite($fp, $data);

            fseek($fp, 0);

            self::addOption('infile', $fp);

            self::addOption('infilesize', strlen($data));

        }

    }


    /**
     * DELETE Request
     *
     * @param string $url  URL
     * @param array  $opts Curl Opts
     *
     * @throws \Exception
     *
     * @return object
     */
    public static function delete($url, Array $opts = [])
    {

        self::addOption('url', $url);

        foreach ($opts as $k => $v) {
            self::addOption($k, $v);
        }

        self::addOption('customrequest', 'DELETE');

        // find & unset bogus content-type header for DELETE request!
        if (!empty(self::$options['CURLOPT_HTTPHEADER'])) {

            foreach (self::$options['CURLOPT_HTTPHEADER'] as $k => $h) {

                if (substr($h, 0, strlen('Content-Type')) == 'Content-Type') {

                    unset(self::$options['CURLOPT_HTTPHEADER'][$k]);

                }

                // Set content length to 0!
                if (substr($h, 0, strlen('Content-Length')) == 'Content-Length') {

                    self::$options['CURLOPT_HTTPHEADER'][$k] = 'Content-Length: 0';

                }

            }

        }

        // find & unset confusing HTTP_METHOD override!
        if (!empty(self::$options['post'])) {

            unset(self::$options['CURLOPT_POST']);

        }

        // find & unset bogus content body for DELETE request!
        if (!empty(self::$options['CURLOPT_POSTFIELDS'])) {

            unset(self::$options['CURLOPT_POSTFIELDS']);

        }

        return self::_call();

    }


    /**
     * Download a file
     *
     * @param string $url      URL
     * @param string $location Where to save?
     * @param array  $opts     Curl Opts
     *
     * @return bool
     *
     * @throws \Exception
     */
    public static function download($url, $location, Array $opts = [])
    {
        self::addOption('url', $url);

        foreach ($opts as $k => $v) {
            self::addOption($k, $v);
        }

        $resp = self::_call();

        file_put_contents($location, $resp);

        return true;
    }

    /**
     * Information for Last Operation
     *
     * @return object
     */
    public static function lastOp()
    {
        if (!self::$_lastOp) {
            trigger_error('Curl did not complete any operation yet!', E_USER_ERROR);
        }
        return (object) self::$_lastOp;
    }


    /**
     * Header parser
     *
     * @param string $data Headers string
     *
     * @return array
     */
    private static function _headers($data)
    {
        $arr = explode("\r\n", $data);
        $rawstatus = array_shift($arr);
        $status = explode(' ', $rawstatus);
        $protocol = array_shift($status);
        $status = implode(' ', $status);

        $res = [
            'status'    => $status,
            'protocol'  => $protocol,
            'rawStatus' => $rawstatus
        ];

        foreach ($arr as $h) {
            $r = explode(':', $h);
            $hn = array_shift($r);
            if (strlen($hn)>0) {
                $res[strtolower($hn)] = trim(implode(' ', $r));
            }
        }

        return $res;
    }


    /**
     * Result getter
     *
     * @return object
     * @throws \Exception
     */
    private static function _getResult()
    {
        $response = self::_call();

        if (isset($response->data->page_count) && $response->data->page_count > 1) {

            $lastUrl = curl_getinfo(self::$_curl, CURLINFO_EFFECTIVE_URL);

            $parsedLastUrl = parse_url($lastUrl);

            if (isset($parsedLastUrl["query"])) {
                $lastUrl .= "&page=%s";
            } else {
                $lastUrl .="?page=%s";
            }

            for ($ii = 1; $ii <= $response->data->page_count; $ii++) {
                self::addOption("url", sprintf(urldecode($lastUrl), $ii));
                $call = self::_call();
                $rows = (array) $call->data->rows + (array) $response->data->rows;
                $response->data->rows = $rows;
            }
        }

        return $response;
    }


    /**
     * Curl reqester
     *
     * @return object
     * @throws \Exception
     */
    private static function _call()
    {
        self::initCurl();

        $responseMap = [
            'application/json'  => 'json',
            'text/x-json'       => 'json',
            'text/xml'          => 'xml',
            'application/xml'   => 'xml',
            'text/csv'          => 'csv',
            'application/octet-stream' => 'csv' // test only
        ];

        self::$_lastOp = (object) [
            'request' => [],
            'code' => 0,
            'handler' => false,
            'headers' => [],
            'body' => 'no-reponse',
            'info'=> (object) []
        ];


        if (self::$options['url'] == null) {

            throw new \Exception('Curl call requires a URL!');

        }

        foreach (self::$options as $k => $v) {

            try {

                curl_setopt(self::$_curl, constant('CURLOPT_'.strtoupper($k)), $v);

            } catch(\Exception $e) {

                throw $e;

            }
        }

        self::$_lastOp->request = self::$options;

        self::$_lastOp->response = curl_exec(self::$_curl);

        try {

            $header_size = curl_getinfo(self::$_curl, CURLINFO_HEADER_SIZE);

            $headerStr = substr(self::$_lastOp->response, 0, $header_size);

            self::$_lastOp->headers = self::_headers($headerStr);

            $body = substr(self::$_lastOp->response, $header_size);

            if (empty(self::$_lastOp->headers['content-type'])) {
                self::$_lastOp->headers['content-type'] = 'text/html';
            }

            $respType = self::$_lastOp->headers['content-type'];

            foreach ($responseMap as $type => $meth) {
                $tgType = strtolower(substr($respType, 0, strlen($type)));
                if (strtolower($type) === $tgType) {
                    $decoderType = 'self::_decode'.ucfirst($meth);
                    $decoded = call_user_func_array($decoderType, [$body]);
                    self::$_lastOp->body = $decoded;
                    self::$_lastOp->handler = $meth;
                }
            }

            if (!self::$_lastOp->handler) {
                self::$_lastOp->body = $body;
            }

            self::$_lastOp->info = curl_getinfo(self::$_curl);

            self::$_lastOp->code = curl_getinfo(self::$_curl, CURLINFO_HTTP_CODE);

            if (empty(self::$_lastOp->response->code)) {
                self::$_lastOp->response->code = 0;
            }

            if (self::$_lastOp->response->code >= 400) {
                throw new \Exception(
                    sprintf('Server Error: %s', self::$_lastOp->response->error)
                );
            } elseif ('20' != substr(self::$_lastOp->response->code, 0, 2)) {
                throw new \Exception(
                    sprintf('Unknown Error: %s', self::$_lastOp->response->error)
                );
            }

        } catch(\Exception $e) {
            self::_error($e);
        }

        return (object) self::$_lastOp;

    }

    /**
     * JSON Parser
     *
     * @param string $string Json content
     *
     * @return mixed
     */
    private static function _decodeJson($string)
    {
        return json_decode($string);
    }

    /**
     * XML Parser
     *
     * @param string $string XML Content
     *
     * @return \SimpleXMLElement
     */
    private static function _decodeXml($string)
    {
        return simplexml_load_string($string);
    }


    /**
     * CSV Parser
     *
     * @param string $string CSV content
     *
     * @return array
     */
    private static function _decodeCsv($string)
    {
        // @TODO: CSV parser regex implement
        $res = [];

        $exp = explode("\n", $string);

        $re = "/^((\"(?:[^\"]|\"\")*\"|[^,]*)(,(\"(?:[^\"]|\"\")*\"|[^,]*))*)$/";

        foreach ($exp as $k => $item) {
            $matches = []; $line = [];
            preg_match($re, $string, $matches);
            foreach ($matches as $match) {
                $line[] = $match[2];
            }
            $res[$k] = $line;
        }

        return $exp;
    }

    /**
     * Error handler. Do not use.
     *
     * @param \Exception $e Exception
     *
     * @throws \Exception
     * @return null
     */
    private static function _error(\Exception $e)
    {
        // @TODO: clean up here!
        throw new \Exception($e->getMessage().' '.$e->getFile().':'.$e->getLine());

    }



    // --------------------- CUSTOM METHODS ---------------- //


    /**
     * PushOver Push service
     *
     * @param string $tx Push title
     * @param array  $d  Push data
     * @param string $u  Attach url
     * @param string $dv Which device to push
     * @param string $t  Auth token
     *
     * @return object
     * @throws \Exception
     */
    public static function pushOver($tx, $d = null, $u = null, $dv = null, $t = null)
    {

        $po_srv = 'https://api.pushover.net/1/messages.json';
        $po_user = 'po-user-hash';
        $po_defr = 'po-user-token';

        $post = [
            'token'   => (is_null($t)?$po_defr:$t),
            'user'    => $po_user,
            'title'   => $tx,
            'message' => strip_tags($d)
        ];

        if ($u) {
            $post['url'] = $u;
        }

        if ($dv) {
            $post['device'] = $dv;
        }

        return self::post($po_srv, $post);
    }

}