<?php
/**
 * PHP version 7
 *
 * Created at 19/03/16 11:10 by yas
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3
 */

namespace App\Utils;

/**
 * Class CacheInterface
 *
 * @category Base
 * @package  App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3app
 */
interface CacheInterface
{

    const TTL = 3600;

    const PREFIX = 'Ev_';


    /**
     * Object getter
     *
     * @param string $key Cache Key
     *
     * @return mixed
     */
    public static function get(string $key);

    /**
     * Object setter
     *
     * @param string $key   Cache Key
     * @param mixed  $value Value to cache
     * @param int    $ttl   TTL
     *
     * @return mixed
     */
    public static function set(string $key, $value, int $ttl);


    /**
     * Object delete
     *
     * @param string $key Cache Key
     *
     * @return mixed
     */
    public static function delete(string $key);


    /**
     * Object checker
     *
     * @param string $key Cache Key
     *
     * @return mixed
     */
    public static function has(string $key);


    /**
     * Delete all cache
     *
     * @return bool
     */
    public static function clean();


}