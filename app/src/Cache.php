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
 * Class Cache
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Cache
{

    const TTL = 3600;

    const PREFIX = 'Ev_';

    /**
     * Cache getter
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public static function get($key)
    {
        return \apcu_fetch(self::PREFIX.$key);
    }

    /**
     * Cache setter
     *
     * @param string $key Key
     * @param string $val Value
     * @param null   $ttl Time
     *
     * @return mixed
     */
    public static function set($key, $val, $ttl = null)
    {
        if (is_null($ttl)) {
            $ttl = self::TTL;
        }

        return \apcu_store(self::PREFIX.$key, $val, $ttl);

    }

    /**
     * Cache check fn.
     *
     * @param string $key Key
     *
     * @return bool
     */
    public static function has($key)
    {

        return \apcu_exists(self::PREFIX.$key);

    }


    /**
     * Cache information
     *
     * @param string $type Cache info
     *
     * @return mixed
     */
    public static function info($type = 'user')
    {
        return \apcu_cache_info($type);
    }


    /**
     * Delete a single cache object
     *
     * @param string $key Key
     *
     * @return mixed
     */
    public static function delete($key)
    {
        return \apcu_delete(self::PREFIX.$key);
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

        return \apcu_clear_cache($type);

    }


    /**
     * Generic Cache Wrapper.
     * Wraps only a callable.
     *
     * @param string   $key Key
     * @param callable $val Data Source / Closure
     * @param integer  $ttl Time to live
     *
     * @return mixed
     */
    public static function via($key, $val, $ttl = 600)
    {
        $cached = self::get((string) $key);

        if (!$cached) {
            $cached = $val();
            self::set((string) $key, $cached, $ttl);
        }

        return $cached;
    }


}