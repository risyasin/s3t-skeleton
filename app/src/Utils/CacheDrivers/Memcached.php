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

namespace App\Utils\CacheDrivers;

use App\Utils\CacheInterface;

/**
 * Class Memcached
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Memcached implements CacheInterface
{

    /* @var \Memcached $_instance */
    private static $_instance;

    /**
     * Cache init
     *
     * @throws \Exception
     * @return \Memcached
     */
    public static function getInstance()
    {

        if (!class_exists('Memcached')) {
            throw new \Exception('Memcached extension is not available!');
        }

        if (!self::$_instance) {

            /* @var \Memcached $_m */
            $m = new \Memcached();

            $m->addServer('127.0.0.1', 11211);

            self::$_instance = $m;

        }

        return self::$_instance;
    }


    /**
     * Cache getter
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public static function get(string $key)
    {

        return self::getInstance()->get(self::PREFIX.$key);

    }

    /**
     * Cache setter
     *
     * @param string $key Key
     * @param string $val Value
     * @param int    $ttl Time
     *
     * @return mixed
     */
    public static function set(string $key, $val, int $ttl = null)
    {
        $m = self::getInstance();

        if (is_null($ttl)) {
            $ttl = self::TTL;
        }

        return $m->set(self::PREFIX.$key, $val, $ttl);

    }

    /**
     * Cache check fn.
     *
     * @param string $key Key
     *
     * @return bool
     */
    public static function has(string $key)
    {
        $m = self::getInstance();

        if (!($m->get(self::PREFIX.$key))) {
            if ($m->getResultCode() != \Memcached::RES_NOTFOUND) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    /**
     * Cache information
     *
     * @return mixed
     */
    public static function info()
    {

        return self::getInstance()->getStats();

    }


    /**
     * Delete a single cache object
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public static function delete(string $key)
    {

        return self::getInstance()->delete(self::PREFIX.$key);

    }


    /**
     * Clears all cached entries
     *
     * @param string $type Cache type
     *
     * @return mixed
     */
    public static function clean($type = 'user')
    {

        return self::getInstance()->flush();

    }


}