<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 06/02/16
 * Time: 00:27
 */

namespace App;


/**
 * Class Cache
 *
 * @package App
 */
class Cache
{

    const TTL = 3600;

    const PREFIX = 'Ev_';

    /**
     * Cache getter
     *
     * @param $key
     * @return mixed
     */
    public static function get($key)
    {
        return \apcu_fetch(self::PREFIX.$key);
    }

    /**
     * Cache setter
     *
     * @param $key
     * @param $val
     * @param null $ttl
     * @return mixed
     */
    public static function set($key, $val, $ttl = null)
    {
        if (is_null($ttl)){ $ttl = self::TTL; }

        return \apcu_store(self::PREFIX.$key, $val, $ttl);

    }

    /**
     * Cache check fn.
     *
     * @param $key
     * @return bool|\string[]
     */
    public static function has($key)
    {

        return \apcu_exists(self::PREFIX.$key);

    }


    /**
     * Cache information
     *
     * @param string $type
     * @return array|bool
     */
    public static function info($type = 'user')
    {
        return \apcu_cache_info($type);
    }


    /**
     * Delete a single cache object
     * @param $key
     * @return bool|\string[]
     */
    public static function delete($key)
    {
        return \apcu_delete(self::PREFIX.$key);
    }


    /**
     * Clears all cached entries
     *
     * @param string $type
     * @return bool
     */
    public static function clean($type = 'user')
    {

        return \apcu_clear_cache($type);

    }




    /**
     * Generic Cache Wrapper.
     * Wraps only a callable.
     *
     * @param string $key
     * @param callable $val
     * @param int $ttl
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