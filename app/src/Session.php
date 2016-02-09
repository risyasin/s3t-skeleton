<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 06/02/16
 * Time: 00:30
 */

namespace App;


/**
 * Class Session
 * @package App
 */
class Session
{

    public static function setHandler()
    {
        // @Todo: Set a proper session handler. memcached?

    }

    public static function has($name) {
        return isset($_SESSION[$name]) ? true : false;
    }

    public static function put($name, $value) {
        return $_SESSION[$name] = $value;
    }

    public static function get($name) {
        return $_SESSION[$name];
    }

    public static function delete($name) {
        if (self::has($name)) {
            unset($_SESSION[$name]);
        }
    }

    public static function flash($name, $string = '') {
        if ( self::has($name) ) {
            $session = self::get($name);
            self::delete($name);
            return $session;
        } else {
            self::put($name, $string);
        }

        return null;
    }

}