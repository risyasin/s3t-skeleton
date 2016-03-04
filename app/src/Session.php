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


/**
 * Class Session
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Session
{

    /**
     * Handler
     *
     * @return null
     */
    public static function setHandler()
    {
        // @Todo: Set a proper session handler. memcached?

    }

    /**
     * Checker
     *
     * @param string $name Key
     *
     * @return bool
     */
    public static function has($name)
    {
        return isset($_SESSION[$name]) ? true : false;
    }

    /**
     * Session setter
     *
     * @param string $name  Key
     * @param mixed  $value Value
     *
     * @return mixed
     */
    public static function put($name, $value)
    {
        return $_SESSION[$name] = $value;
    }


    /**
     * Getter
     *
     * @param string $name Key
     *
     * @return mixed
     */
    public static function get($name)
    {
        return $_SESSION[$name];
    }


    /**
     * Destroy
     *
     * @param string $name Key
     *
     * @return null
     */
    public static function delete($name)
    {
        if (self::has($name)) {
            unset($_SESSION[$name]);
        }
    }


    /**
     * Increment w/o decrement.
     *
     * @param string $name Key
     *
     * @return int
     */
    public static function increment($name)
    {
        if (self::has($name)) {
            return $_SESSION[$name]++;
        } else {
            return $_SESSION[$name] = 0;
        }
    }


    /**
     * Session Flash
     *
     * @param string $name   Key
     * @param string $string Value
     *
     * @return mixed|null
     */
    public static function flash($name, $string = '')
    {
        if (self::has($name)) {

            $session = self::get($name);
            self::delete($name);

            return $session;
        } else {

            self::put($name, $string);

        }

        return null;
    }

}