<?php
/**
 * PHP version 7
 *
 * Created at 26/03/16 11:56 by yas
 *
 * @category Base
 * @package App\Utils
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3
 */

namespace App\Utils;

use App\Base;


/**
 * Class TwigDbAccess
 *
 * @category Base
 * @package  App\Utils\TwigDbAccess
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class TwigDbAccess
{


    private $_current = null;

    /**
     * TwigDbAccess constructor.
     */
    public function __construct()
    {
        // Base::barDump('called TwigDbAccess');

        return $this;
    }


    /**
     * Call proxy
     *
     * @param string $m   Method
     * @param array  $arg Arguments
     *
     * @return self
     */
    public function __call($m, $arg)
    {

        if (class_exists(Base::MODEL_NS.$m)) {

            $this->_current = Base::MODEL_NS.$m;

            return $this;

        } else {

            if (method_exists($this->_current, $m)) {

                return call_user_func_array([$this->_current, $m], $arg);

            } else {

                return $this;

            }
        }
    }


    /**
     * Proxy for models
     *
     * @param string $prop Property Access
     *
     * @return self
     */
    public function __get($prop)
    {
        Base::barDump('called prop '.$prop);
        return $prop;

    }

}