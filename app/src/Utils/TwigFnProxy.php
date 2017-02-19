<?php
/**
 * PHP version 7
 *
 * Created at 26/03/16 11:56 by yas
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3
 */

namespace App\Utils;

use App\Base;


/**
 * Class TwigFnProxy
 *
 * @category Base
 * @package  App\Utils\TwigFnProxy
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class TwigFnProxy
{


    private $_current = null;

    /**
     * TwigFnProxy constructor.
     *
     * @param string $class Class
     */
    public function __construct($class = null)
    {
        if (class_exists('App\\'.$class)) {
            $this->_current = 'App\\'.$class;
        }

        return $this;
    }


    /**
     * Call proxy
     *
     * @param string $func Method
     * @param array  $args Arguments
     *
     * @return self
     */
    public function __call($func, $args)
    {
        if ($this->_current ?? false) {

            return call_user_func_array([$this->_current, $func], $args);

        } else {

            if (function_exists($func)) {

                return $func(...$args);

            } else {

                return null;

            }
        }

    }


    /**
     * Proxy for static methods/classes
     *
     * @param string $prop Property Access
     *
     * @return self
     */
    public function __get($prop)
    {
        return $prop;
    }

}