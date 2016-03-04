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


use RedBeanPHP\OODBBean;
use RedBeanPHP\R;
use Goutte\Client as MyClient;


/**
 * Class Tools
 * Tooling methods should be placed here
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */

class Tools
{

    const ARR_PATH_SEP = '.';

    /* @var array $_cache Cache collection */
    private static $_cache = [];


    /**
     * Simple convenience function, returns ISO date formatted
     * representation of $time.
     *
     * @param null $time Timestamp
     *
     * @return bool|string
     */
    public static function isoDate( $time = null )
    {
        if (!$time) {
            $time = time();
        }

        return date('Y-m-d', $time);
    }

    /**
     * Simple convenience function,
     * returns ISO date time formatted representation of $time.
     *
     * @param mixed $time UNIX timestamp
     *
     * @return string
     */
    public static function isoDateTime($time = null)
    {
        if (!$time) {
            $time = time();
        }

        return date('Y-m-d H:i:s', $time);
    }


    /**
     * Sample text generator
     *
     * @param int  $length Length
     * @param bool $html   html output?
     *
     * @return string
     */
    public static function generateText($length = 2000, $html = false)
    {

        $res = ''; $i = 0;

        if (!self::$_cache['genLines']) {
            self::$_cache['genLines'] = explode(
                "\n", file_get_contents(_DROOT.'/app/data/words.txt')
            );
        }


        while ($i < $length) {

            $line = self::$_cache['genLines'][array_rand(self::$_cache['genLines'])];

            if ($html) {
                $res = '<p class="generated">'.$res."\n".$line.'</p>';
            } else {
                $res = $res.' '.$line;
            }

            $i = strlen($res);
        }

        return $res;
    }


    /**
     * Slugify function. transliterates non-latin,
     * removes punctuation etc. Uses ICU ext.
     *
     * @param string $text  Text to slugify
     * @param string $sep   Separator
     * @param null   $unset Unset which?
     *
     * @return mixed
     */
    public static function slugify($text, $sep = '-', $unset = null)
    {

        $rules = [
            'latin' => 'Any-Latin;',
            'nfd' => 'NFD;',
            'noSpace' => '[:Nonspacing Mark:] Remove;',
            'nfc' => 'NFC;',
            'punc' => '[:Punctuation:] Remove;',
            'lower' => 'Lower();'
        ];

        if ($unset) {
            unset($rules[$unset]);
        }

        $rule = implode(' ', $rules);
        // DEF RULE = $rule = "Any-Latin; NFD;
        // [:Nonspacing Mark:] Remove; NFC;
        // [:Punctuation:] Remove; Lower();";

        $text = transliterator_transliterate($rule, $text);

        return str_replace(' ', $sep, $text);
    }


    /**
     * Old Slugify function. transliterates non-latin, removes punctuation etc.
     *
     * @param string $text Text to slugify
     * @param string $sep  Separator
     *
     * @return bool|mixed|string
     */
    public static function slugifyOld($text, $sep = '-')
    {
        $text = preg_replace('~[^\\pL\d]+~u', $sep, $text);
        $text = trim($text, $sep);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text)) {
            return false;
        }
        return $text;
    }

    /**
     * Value by Path
     *
     * @param mixed  $arr  Array
     * @param string $path Path definition by self::ARR_PATH_SEP
     *
     * @return null
     */
    public static function getByPath($arr, $path)
    {

        if (!$path) {
            return null;
        }

        $segments = is_array($path) ? $path : explode(self::ARR_PATH_SEP, $path);

        $cur =& $arr;

        foreach ($segments as $segment) {
            if (!isset($cur[$segment])) {
                return null;
            }
            $cur = $cur[$segment];
        }

        return $cur;

    }


    /**
     * Set By Path
     *
     * @param mixed  $arr   Array
     * @param string $path  Path
     * @param string $value New value
     *
     * @return null
     */
    public static function setByPath(&$arr, $path, $value)
    {
        if (!$path) {
            return null;
        }

        $segments = is_array($path) ? $path : explode(self::ARR_PATH_SEP, $path);

        $cur =& $arr;

        foreach ($segments as $segment) {
            if (!isset($cur[$segment])) {
                $cur[$segment] = [];
            }
            $cur =& $cur[$segment];
        }

        $cur = $value;

    }


    /**
     * Fetches & caches Weather status from
     * mynet.com/havadurumu/asya/turkiye/izmir
     *
     * @return mixed
     */
    public static function weatherStatus()
    {

        if (!Cache::get('weatherStatus')) {

            $client = new MyClient();

            $crawler = $client->request(
                'get', 'http://www.mynet.com/havadurumu/asya/turkiye/izmir'
            );

            $res = $crawler->filter(
                '.hvContLeftCity p.hvPboxMiddle'
            )->each(
                function ($node) {
                    /* @var \Symfony\Component\DomCrawler\Crawler $node */
                    $imgUrl = $node->filter('img')->attr('src');
                    return [
                        'day' => $node->filter('span.hvDay')->text(),
                        'text' => $node->filter('span.hvMood')->text(),
                        'class' => substr($imgUrl, strrpos($imgUrl, '/')+1),
                        'high' => $node->filter('span.hvDeg1')->text(),
                        'low' => $node->filter('span.hvDeg2')->text(),
                        'orgImgUrl' => $imgUrl,
                    ];

                }
            );

            Cache::set('weatherStatus', $res, (3600*3)+16); // 3 hours & 16 seconds
        }

        return Cache::get('weatherStatus');
    }



    /**
     * Fetches & caches exchange rates from http://finans.mynet.com/borsa/
     *
     * @return array
     */
    public static function exchangeRates()
    {
        // Cache::delete('exchangeRates');
        if (!Cache::get('exchangeRates')) {
            $client = new MyClient();
            $crawler = $client->request('get', 'http://finans.mynet.com/borsa/');

            $res = $crawler->filter('.fnImkbData li a')->each(
                function ($node) {

                    /* @var \Symfony\Component\DomCrawler\Crawler $node */
                    $name = $node->filter('strong')->text();
                    list($val, $change) = explode(
                        ' ',
                        trim(
                            preg_replace(
                                '/\<strong\>[a-zA-Zıöüçş-]+\<\/strong\>/',
                                '',
                                $node->html()
                            )
                        )
                    );

                    return [
                        'name' => $name,
                        'val' => self::clearInvisible($val),
                        // get high ascii invisibles like 194 or 160 etc.
                        'change' => self::clearInvisible($change)
                    ];
                }
            );
            Cache::set('exchangeRates', $res, (3600*3)+127); // 3 hours & 127 secs.
        }

        return Cache::get('exchangeRates');
    }


    /**
     * Remove High Ascii characters that can not be printed
     *
     * @param string  $str String
     * @param integer $r   lower limit of ascii
     *
     * @return string
     */
    public static function clearInvisible($str, $r = 155)
    {
        $ret = [];
        for ($i = 0; $i < strlen($str); $i++) {
            if (ord($str[$i]) < $r) {
                $ret[] = $str[$i];
            }
        }
        return implode('', $ret);
    }


    /**
     * Array to bean in loop
     *
     * @param OODBBean $r    Bean
     * @param array    $data Data
     *
     * @return mixed
     */
    public static function importFromObj($r, $data)
    {
        $beanType = $r->getPropertiesAndType()[1];

        foreach ($data as $k => $v) {
            if (is_object($v)) {
                if (!empty($v->value)) {
                    $r->{$k} = $v->value;
                } else {
                    // can not be processed here.
                    // we need more sources
                }
            } else {
                $r->{$k} = $v;
            }
        }
        return $r;
    }


    /**
     * Model agnostic m2m fn
     *
     * @param string $type Model name
     * @param object $data Data
     *
     * @return null
     */
    public static function relM2m($type, $data)
    {
        $criteria = $data->{$type};
        $rso  = R::findOne(
            $type, 'WHERE `'.key($criteria).'` = "'.current($criteria).'"'
        );

        // if only rso (relation source obj exists)
        if ($rso && $data->m2m) {
            foreach ($data->m2m as $rel) {
                $type = key($rel);
                $query = 'WHERE `'.key($rel->{$type}).'` = '.
                    '"'.current($rel->{$type}).'"';
                $ro  = R::findOne($type, $query);
                // if only ro (relation obj exists)
                if ($ro) {
                    $rso['shared'.ucfirst($type)][] = $ro;
                }
            }
            R::store($rso);
        }
    }


    /**
     * Get JSON content
     *
     * @param string $file file path
     *
     * @return mixed|null|string
     */
    public static function getJsonFile($file)
    {
        if (!is_file($file) || !is_readable($file)) {
            return null;
        }

        // @Weak: can not decode large file. maybe read via an iterator?
        $content = file_get_contents($file);

        try {
            $content = json_decode($content);
        } catch(\Exception $e) {
            $msg = 'Unable to parse file: '.$file.' - '.json_last_error_msg();
            Base::discard(new \Exception($msg));
        }
        return $content;
    }


    /**
     * JSON importer
     *
     * @param string $modelName Model name
     * @param string $jsonFile  Json path
     *
     * @return array|bool|\Exception
     */
    public static function importJSON($modelName, $jsonFile)
    {
        try {
            $data = self::getJsonFile($jsonFile);
        } catch(\Exception $e) {
            return [$e, json_last_error_msg()];
        }


        try {
            /* @var \App\AbstractModel $model */
            $model = Base::MODEL_NS.ucfirst($modelName);

            // Direct tables.
            if (!strstr($jsonFile, '_')) {

                foreach ($data as $item) {
                    /* @var \RedBeanPHP\OODBBean $r */
                    $r = $model::create();
                    $id = $model::save(self::importFromObj($r, $item));

                    // Many-to-one Relations
                    foreach ($item as $k => $v) {
                        if (is_object($v) && !empty($v->rel)) {
                            list($src, $where) = $v->rel;
                            /* @var \App\AbstractModel $src  */
                            $src = Base::MODEL_NS.ucfirst($src);
                            $parent = $src::findOne('WHERE '.$where.'');
                            $cur = $model::load($id);
                            $cur[$k] = $parent;
                            R::store($cur);
                        }
                    }
                }

            } else { // Relational data
                foreach ($data as $h) {
                    if (!empty($h->m2m)) {
                        self::relM2m(strtolower($modelName), $h);
                    }
                }
            }

        } catch(\Exception $e) {
            return $e;
        }

        return true;
    }


    /**
     * Data to Json
     *
     * @param string $model Model to export
     *
     * @return string
     */
    public static function exportAsJson($model)
    {
        $data = [];

        /* @var AbstractModel $model */
        $model = Base::MODEL_NS.$model;

        $mm  = $model::findAll();

        // $rels = $model::getRelations();

        foreach ($mm as $k => $bean) {
            /* @var \RedBeanPHP\OODBBean $bean */
            $meta = $bean->getMeta('sys.orig');

            Base::dump($meta);

            $obj = $bean->export();

            unset($obj['id']);

            $data[] = $obj;
            // $data[$k.'-p'] = $bean->parent;
        }

        return $data;
    }


    /**
     * Test given string whether it's json.
     *
     * @param mixed $string Json str
     *
     * @return bool
     */
    public static function isJson($string)
    {
        return !preg_match(
            '/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/',
            preg_replace(
                '/"(\\.|[^"\\\\])*"/', '', $string
            )
        );
    }


    /**
     * Existence check
     *
     * @param string $string String
     *
     * @return bool
     */
    public static function isEmpty($string)
    {
        $string = preg_replace('/\s+/', '', trim($string));
        return (empty($string) || $string == '');
    }


    /**
     * Parses PhpDoc Block Comments. TESTING!
     *
     * @param string $doc Doc str
     *
     * @return array
     */
    public static function parseDocBlock($doc)
    {
        $result = [];
        if (preg_match_all(
            '/\* @(\w+)\s+([\\|a-z]+)\s+|([\$|a-z]+)\s+(.*)?/mi', $doc, $matches
        )) {
            $result = array_combine($matches[1], $matches[2]);
        }

        return $result;
    }


    /**
     * Array code generator
     *
     * @param string $name Var name
     * @param array  $arr  Arr data
     * @param string $tk   Key name
     * @param array  $val  Values
     *
     * @return string
     */
    public static function arrMapGen($name, $arr, $tk = 'k', $val = [])
    {
        $code = [ '$'.$name.' = ['];

        foreach ($arr as $k => $v) {
            $tb = ${$tk};
            if (!empty($val[$tb])) {
                $code[$tb] = "\t'".$tb."' => '".$val[$tb]."',";
            } else {
                $code[$tb] = "\t'".$tb."' => '',";
            }
        }

        array_push($code, '];');

        return implode("\r\n", $code);
    }



    /**
     * Simple file type by extension.
     *
     * @param string $filePath File
     *
     * @return mixed|string
     */
    public static function getMimeType($filePath)
    {
        $extMap = [
            'js' => 'text/javascript',
            'csv' => 'application/csv',
            'xml' => 'application/xml',
            'txt' => 'text/plain',
            'css' => 'text/css',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'woff' => 'application/font-woff',
            'woff2' => 'application/font-woff2'
        ];

        $ext = pathinfo($filePath, PATHINFO_EXTENSION);

        if (array_key_exists($ext, $extMap)) {

            return $extMap[$ext];

        } else {

            $type = 'text/html';

            $fInfo = finfo_open(FILEINFO_MIME_TYPE);

            foreach (glob("*") as $filePath) {
                $type  = finfo_file($fInfo, $filePath);
            }

            finfo_close($fInfo);

            return $type;
        }

    }

}