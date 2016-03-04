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

use Slim\Container;

/**
 * Class AbstractModule
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */

abstract class AbstractModule
{

    public $requires = [];

    /* @var $view \Slim\Views\Twig */
    protected $view;

    /* @var $data array */
    public $data;


    /**
     * AbstractModule constructor.
     *
     * @param Container $c   DI Container
     * @param array     $cfg Config Array
     */
    public function __construct(Container $c, array $cfg)
    {

        Base::set('moduleName', get_called_class());

    }

    /**
     * Abstract Module registerer
     *
     * @return mixed
     */
    abstract public function register();


}