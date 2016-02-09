<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 06/02/16
 * Time: 22:15
 */

namespace App;


use RedBeanPHP\R;

class Tools
{

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


    public static function DTnow()
    {
        return date('Y-m-d H:i:s', time());
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


    public static function slugify($text, $sep = '-')
    {
        $rule = "Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();";

        $p = transliterator_transliterate($rule, $text);

        return str_replace(' ', $sep, trim($p));

    }


    public static function slugifyOld($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

        // trim
        $text = trim($text, '-');

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // lowercase
        $text = strtolower($text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        if (empty($text))
        {
            return 'n-a';
        }

        return $text;
    }


}