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

    /* @var $data array  */
    public $data;

    public function __construct()
    {

        $this->view = Base::$c->get('view');
        $this->logger = Base::$c->get('logger');
        $this->debugbar = Base::$c->get('debugbar');

        Base::set('actionName', get_called_class());

    }


    public function log($log = null)
    {
        if ($this->debugbar){
            /* @var $this->debugbar \DebugBar\StandardDebugBar */
            $this->debugbar['messages']->error($log);
        } else {
            $this->logger->info($log);
        }
    }

    public function errlog($log)
    {
        if ($this->debugbar){
            /* @var $this->debugbar \DebugBar\StandardDebugBar */
            $this->debugbar['messages']->error($log);
        } else {
            $this->logger->error($log);
        }
    }

}