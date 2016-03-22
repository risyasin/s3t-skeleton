<?php
/**
 * PHP version 7
 *
 * Created at 20/03/16 11:27 by yas
 *
 * @category Base
 * @package App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3
 */

namespace App\Utils;

/**
 * Class Cache
 *
 * @category Base
 * @package  App\Cache
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Cache extends CacheDrivers\Memcached
{


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
    public static function via(string $key, callable $val, int $ttl = 3600)
    {
        $cached = self::get((string) $key);

        if (!$cached) {
            $cached = $val();
            self::set((string) $key, $cached, $ttl);
        }

        return $cached;
    }


}