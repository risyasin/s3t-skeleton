<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 06/02/16
 * Time: 22:15
 */

namespace App;


use RedBeanPHP\R;
use Goutte\Client as MyClient;


/**
 * Class Tools
 * @package App
 */
class Tools
{

    const ARR_PATH_SEP = '.';


    private static $cache = [];

    /**
     * Processes menu array for twig
     *
     * @param $menu
     * @return array
     */
    public static function makeTree($menu)
    {
        $arr = [];

        foreach($menu as $k => $item) {

            /* @var \App\Models\Menu $item */
            $item = $item->export();

            if ($item['parent'] == 0){

                if(empty($arr[$k])){

                    $arr[$k] = $item;

                    if(empty($arr[$k]['children'])){
                        $arr[$k]['children'] = [];
                    }
                }

            } else {

                if(empty($arr[$item['parent']])){
                    /* @var \App\Models\Menu $parent */

                    $parent = $menu[$item['parent']];

                    $arr[$item['parent']] = $parent->export();

                    if(empty($arr[$item['parent']]['children'])){
                        $arr[$item['parent']]['children'] = [];
                    }

                    $arr[$item['parent']]['children'][] = $item;

                } else {

                    $arr[$item['parent']]['children'][] = $item;

                }
            }

        }

        return $arr;

    }



    /**
     * Simple convenience function, returns ISO date formatted representation
     * of $time.
     *
     * @param mixed $time UNIX timestamp
     *
     * @return string
     */
    public static function isoDate( $time = NULL )
    {
        if ( !$time ) {
            $time = time();
        }

        return @date( 'Y-m-d', $time );
    }

    /**
     * Simple convenience function, returns ISO date time
     * formatted representation
     * of $time.
     *
     * @param mixed $time UNIX timestamp
     *
     * @return string
     */
    public static function isoDateTime( $time = NULL )
    {
        if ( !$time ) $time = time();
        return @date( 'Y-m-d H:i:s', $time );
    }



    public static function generateText($length = 2000, $html = false)
    {


        $res = ''; $i = 0;

        if (!self::$cache['genlines']){
            self::$cache['genlines'] = explode("\n", file_get_contents(_DROOT.'/app/data/words.txt'));
        }


        while($i < $length){

            $line = self::$cache['genlines'][array_rand(self::$cache['genlines'])];

            if ($html){
                $res = '<p class="generated">'.$res."\n".$line.'</p>';
            } else {
                $res = $res.' '.$line;
            }

            $i = strlen($res);
        }

        return $res;
    }



    /**
     * Slugify function. translits non-latin, removes punctuation etc. Uses ICU ext.
     *
     * @param $text
     * @param string $sep
     * @param null $unset
     * @return mixed
     */
    public static function slugify($text, $sep = '-', $unset = null)
    {

        $rules = [
            'latin' => 'Any-Latin;',
            'nfd' => 'NFD;',
            'nospace' => '[:Nonspacing Mark:] Remove;',
            'nfc' => 'NFC;',
            'punc' => '[:Punctuation:] Remove;',
            'lower' => 'Lower();'
        ];

        if ($unset){ unset($rules[$unset]); }

        $rule = implode(' ', $rules);
        // DEF RULE = $rule = "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();";
        $text = transliterator_transliterate($rule, $text);
        return str_replace(' ', $sep, $text);
    }


    /**
     * Old Slugify function. transliterates non-latin, removes punctuation etc.
     *
     * @param $text
     * @param string $sep
     * @return bool|mixed|string
     */
    public static function slugifyOld($text, $sep = '-')
    {
        $text = preg_replace('~[^\\pL\d]+~u', $sep, $text);
        $text = trim($text, $sep);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = strtolower($text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        if (empty($text)) { return false; }
        return $text;
    }

    /**
     * @param $arr
     * @param $path
     * @return null
     */
    public static function getByPath($arr, $path)
    {

        if (!$path) { return null; }

        $segments = is_array($path) ? $path : explode(self::ARR_PATH_SEP, $path);
        $cur =& $arr;

        foreach ($segments as $segment) {
            if (!isset($cur[$segment]))
                return null;

            $cur = $cur[$segment];
        }

        return $cur;

    }


    /**
     * @param $arr
     * @param $path
     * @param $value
     * @return null
     */
    public static function setByPath(&$arr, $path, $value)
    {
        if (!$path) { return null; }

        $segments = is_array($path) ? $path : explode(self::ARR_PATH_SEP, $path);
        $cur =& $arr;
        foreach ($segments as $segment) {
            if (!isset($cur[$segment]))
                $cur[$segment] = array();
            $cur =& $cur[$segment];
        }
        $cur = $value;
    }




    /**
     * Fetches & caches Weather status from mynet.com/havadurumu/asya/turkiye/izmir
     *
     * @return mixed
     */
    public static function weatherStatus()
    {
        // Cache::delete('weatherStatus');
        // Skycon map
        // "clear-day", "clear-night", "partly-cloudy-day", "partly-cloudy-night",
        // "cloudy", "rain", "sleet", "snow", "wind", "fog"
        $map = [
            '02.gif' => 'rain', // Sağanak Yağışlı
            '03.gif' => 'rain', // Sağanak Yağışlı
            '04.gif' => 'rain', // Gök Gürültülü Sağanak Yağışlı,
            '05.gif' => 'snow', // Karla Karışık Yağmur
            '06.gif' => 'snow', //
            '09.gif' => 'rain', //
            '11.gif' => 'rain', // Sağanak Yağışlı
            '12.gif' => 'rain', // Yağışlı
            '13.gif' => 'snow', //
            '14.gif' => 'snow', //
            '15.gif' => 'snow', //
            '17.gif' => 'rain', //
            '19.gif' => 'fog', //
            '20.gif' => 'sleet', //
            '21.gif' => 'sleet', //
            '22.gif' => 'partly-cloudy-day', //
            '23.gif' => 'wind', //
            '26.gif' => 'partly-cloudy-day', // Çok Bulutlu,
            '27.gif' => 'rain', // Gök Gürültülü Yağışlı
            '28.gif' => 'cloudy', // Parçalı Bulutlu
            '30.gif' => 'clear-day', // Az Bulutlu
            '32.gif' => 'clear-day', // Açık
            '34.gif' => 'clear-day', // Açık
            '35.gif' => 'rain',
            '39.gif' => 'rain',
            '40.gif' => 'rain',
            '42.gif' => 'snow',
            '43.gif' => 'wind'
        ];

        if (!Cache::get('weatherStatus')){
            $client = new MyClient();
            $crawler = $client->request('get', 'http://www.mynet.com/havadurumu/asya/turkiye/izmir');
            $res = $crawler->filter('.hvContLeftCity p.hvPboxMiddle')->each(function ($node) use($map) {
                $imgUrl = $node->filter('img')->attr('src');
                $imgName = substr($imgUrl, strrpos($imgUrl, '/')+1);
                return [
                    'day' => $node->filter('span.hvDay')->text(),
                    'text' => $node->filter('span.hvMood')->text(),
                    'class' => !empty($map[$imgName])?$map[$imgName]:'clear',
                    'high' => $node->filter('span.hvDeg1')->text(),
                    'low' => $node->filter('span.hvDeg2')->text(),
                    'image' => $imgName,
                    'orgImgUrl' => $imgUrl,
                ];
            });
            Cache::set('weatherStatus', $res, (3600*3)+16); // 3 hours & 16 seconds
        }
        // --
        return Cache::get('weatherStatus');
    }



    /**
     * Fetches & caches exchange rates from http://finans.mynet.com/borsa/
     *
     * @return mixed
     */
    public static function exchangeRates()
    {
        // Cache::delete('exchangeRates');
        if (!Cache::get('exchangeRates')){
            $client = new MyClient();
            $crawler = $client->request('get', 'http://finans.mynet.com/borsa/');
            $res = $crawler->filter('.fnImkbData li a')->each(function ($node) {
                /* @var \Symfony\Component\DomCrawler\Crawler $node */
                $name = $node->filter('strong')->text();
                list($val, $change) = explode(' ', trim(preg_replace('/\<strong\>[a-zA-Zıöüçş-]+\<\/strong\>/', '', $node->html())));
                return [
                    'name' => $name,
                    'val' => self::clearInvisible($val), // get high ascii invisibles like 194 or 160 etc.
                    'change' => self::clearInvisible($change)
                ];
            });
            Cache::set('exchangeRates', $res, (3600*3)+127); // 3 hours & 127 secs.
        }
        return Cache::get('exchangeRates');
    }


    /**
     * Remove High Ascii characters that can not be printed
     *
     * @param $str
     * @param int $r
     * @return string
     */
    public static function clearInvisible($str, $r = 155)
    {
        $ret = [];
        for($i = 0; $i < strlen($str); $i++) {
            if (ord($str[$i]) < $r){
                $ret[] = $str[$i];
            }
        }
        return implode('', $ret);
    }




    /**
     * Array to bean in loop
     *
     * @param \RedBeanPHP\OODBBean $r
     * @param \stdClass $data
     * @return mixed
     */
    private static function importFromObj($r, $data)
    {
        $beanType = $r->getPropertiesAndType()[1];

        foreach($data as $k => $v) {
            if (is_object($v)){
                if(!empty($v->value)){
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
     * @param $type
     * @param $data
     */
    public static function relM2m($type, $data)
    {
        $crit = $data->{$type};
        $rso  = R::findOne($type, 'WHERE `'.key($crit).'` = "'.current($crit).'"');

        // if only rso (relation source obj exists)
        if ($rso && $data->m2m){
            foreach($data->m2m as $rel) {
                $type = key($rel);
                $query = 'WHERE `'.key($rel->{$type}).'` = "'.current($rel->{$type}).'"';
                $ro  = R::findOne($type, $query);
                // if only ro (relation obj exists)
                if ($ro){ $rso['shared'.ucfirst($type)][] = $ro; }
            }
            R::store($rso);
        }
    }


    /**
     * @param $file
     * @return mixed|null|string
     */
    public static function getJsonFile($file)
    {
        if (!is_file($file) || !is_readable($file)){ return null; }
        // @Weak: can not decode large file. maybe read via an iterator?
        $content = file_get_contents($file);
        try {
            $content = json_decode($content);
        } catch(\Exception $e) {
            Base::discard(new \Exception('Unable to parse file: '.$file.' - '.json_last_error_msg()));
        }
        return $content;
    }



    /**
     * @param $modelName
     * @param $jsonFile
     * @return array|bool
     */
    public static function importJSON($modelName, $jsonFile)
    {
        try {
            $data = self::getJsonFile($jsonFile);
        } catch(\Exception $e) {
            return [$e, json_last_error_msg()];
        }


        try {
            /* @var \Ra\Sc\AbstractModel $model */
            $model = Base::MODEL_NS.ucfirst($modelName);

            // Direct tables.
            if (!strstr($jsonFile, '_')){

                foreach($data as $item) {
                    /* @var \RedBeanPHP\OODBBean $r */
                    $r = $model::create();
                    $id = $model::save(self::importFromObj($r, $item));

                    // Many-to-one Relations
                    foreach($item as $k => $v) {
                        if (is_object($v) && !empty($v->rel)) {
                            list($src, $where) = $v->rel;
                            /* @var \Ra\Sc\AbstractModel $src  */
                            $src = Base::MODEL_NS.ucfirst($src);
                            $parent = $src::findOne('WHERE '.$where.'');
                            $cur = $model::load($id);
                            $cur[$k] = $parent;
                            R::store($cur);
                        }
                    }
                }

            } else { // Relational data
                foreach($data as $h) {
                    if(!empty($h->m2m)) {
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
     * @param $model
     * @return array
     */
    public static function exportAsJson($model)
    {
        $data = [];

        /** @var AbstractModel $model */
        $model = Base::MODEL_NS.$model;

        $mm  = $model::findAll();

        $rels = $model::getRelations();

        foreach($mm as $k => $bean) {
            /** @var \RedBeanPHP\OODBBean $bean */
            $meta = $bean->getMeta('sys.orig');

//            echo '<pre style="font: normal 12px Lucida Sans;">';
//            print_r($meta);
//            echo '</pre>'; exit;
//
            $obj = $bean->export();

            unset($obj['id']);

            $data[] = $obj;
            // $data[$k.'-p'] = $bean->parent;
        }

        return $data;
    }


    /**
     * @param $data
     * @return string
     */
    public static function jsonEncode($data)
    {
        return json_encode($data);
    }


    /**
     * @param $json
     * @return mixed
     */
    public static function jsonDecode($json)
    {
        return json_decode($json);
    }



    /**
     * Test given string whether it's json.
     * @param $string
     * @return bool
     */
    public static function isJson($string)
    {
        return !preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/',
                           preg_replace('/"(\\.|[^"\\\\])*"/', '', $string));
    }


    /**
     * @param $string
     * @return bool
     */
    public static function isEmpty($string)
    {
        $string = preg_replace('/\s+/', '', trim($string));
        return (empty($string) || $string == '');
    }


    /**
     * Parses PhpDoc Block Comments. TESTING!
     * @param $doc
     * @return array
     */
    public static function parseDocBlock($doc)
    {
        $result = [];
        if (preg_match_all('/\* @(\w+)\s+([\\|a-z]+)\s+|([\$|a-z]+)\s+(.*)?/mi', $doc, $matches)){
            $result = array_combine($matches[1], $matches[2]);
        }

        return $result;
    }


    /**
     * @param $name
     * @param $arr
     * @param string $tk
     * @param array $val
     * @return string
     */
    public static function arrMapGen($name, $arr, $tk = 'k', $val = [])
    {
        $code = [ '$'.$name.' = ['];

        foreach($arr as $k => $v) {
            $tb = ${$tk};
            if(!empty($val[$tb])) {
                $code[$tb] = "\t'".$tb."' => '".$val[$tb]."',";
            } else {
                $code[$tb] = "\t'".$tb."' => '',";
            }
        }

        array_push($code, '];');

        return implode("\r\n", $code);
    }


}