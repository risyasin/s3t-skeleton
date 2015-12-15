<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 14/12/15
 * Time: 22:52
 */

namespace App;


class AbstractAction
{

    /* @var $view \Slim\Views\Twig */
    protected $view;
    /* @var $logger \Monolog\Logger */
    protected $logger;
    /* @var $debugbar \DebugBar\StandardDebugBar */
    protected $debugbar;

    public function __construct($c)
    {
        /* @var $c \Slim\Container  */

        $this->view = $c->get('view');
        $this->logger = $c->get('logger');
        $this->debugbar = $c->get('debugbar');

    }

}