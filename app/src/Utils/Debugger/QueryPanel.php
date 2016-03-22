<?php
/**
 * PHP version 7
 *
 * Created at 18/03/16 by yas 
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/base3app
 */

namespace App\Utils\Debugger;

use Tracy\IBarPanel;
use RedBeanPHP\R as R;

/**
 * Class QueryPanel Add Redbean query logs to Tracy Bar
 *
 * @category Base
 * @package  App\QueryPanel
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class QueryPanel implements IBarPanel
{

    /**
     * Return bar label
     *
     * @return string
     */
    public function getTab()
    {

        // DB image icon
        $dbIcon = 'data:image/png;base64,'.
        'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACCUlEQVQ4jaXRzU4TURjG8f'.
        '+cOdOPqRxaqAFcNIBEhK0kNPEK8CLcQ7wBlu68AY1oookb3BqXrl0JbuiYCCwoAmOwnRkm'.
        'oNOZzhwXtmgDMX6c5EnenOQ8yfs78J/H6A/NZrOWaX03CILbR6470/a88Uq5bF+fnsayrK'.
        '+e73/2fH83juO3lmm+uLO0tD9QsLHx/kQIQ2lAZ5osy0izlFa7Rc6yuFIqkbMsTCHQEN5a'.
        'WBgGkP2C4y/HyjQltm0zMT7O2NhVgiDAcRrs7O4yNDTEtYkJarUa3W6q+u/OCzSQxAlSSa'.
        'rVUYQQnJ6doZQil8uxt7dHGIYopSgUi+cGAuDps+cYEF4Q0hqt9UU4wwgfrT0BwAR4/eoV'.
        '9Xp91XGcfBR9o1DIAxAEAUeuy+HhIb7nkSQJJ0EAWdZZWV5+MLBCbXJSKTXM6dkpmxubAP'.
        'i+x8ftbdrtNrZtU6lUmJufZ2Rk9KKBNCWj1So3ZmeZuzmLZUn2P+2ztdVgq9HAdV2EaVIq'.
        'lTDE+ef9NAB9iQGXGgDhw8drgwaLi/XVD46T70QR+fzvDbI07dxbWRk0mJyaUuXh8g+Dzb'.
        '83kP9gIIGu6A0zSRJHf2qQxEkEzADS7N81m813UlqpMAyKxaKVZanp+77ZM4hbrdZJJ4p2'.
        'fM9783J9/f7BwYEDRMYvxaIXCRR66a/YBaJeukDWC98BVAIMWkaJtDsAAAAASUVORK5CYII';

        return '<span title="RedBean Queries"><img src="'.$dbIcon.'"> PDO</span>';

    }


    /**
     * Panel html
     *
     * @return string
     */
    public function getPanel()
    {
        /* @var \RedBeanPHP\Logger\RDefault $logs */
        $logs = R::getDatabaseAdapter()->getDatabase()->getLogger();

        ob_start(); ?>
        <div class="nette-inner">
            <?php echo self::_printLogs($logs->getLogs()); ?>
         </div>
        <?php
        $var = ob_get_clean();

        return '<h3>All Queries</h3>'.$var;
    }


    /**
     * Log string formatter
     *
     * @param array $log Log dump
     *
     * @return string
     */
    private function _printLogs($log)
    {
        $res = '<ul style="margin: 0 10px;">';

        foreach ($log as $line) {
            if (is_array($line) || is_object($line)) {
                $res.='<li>'.json_encode($line).'</li>';
            } else {
                $res.='<li>'.$line.'</li>';
            }
        }

        return $res.'</ul>';
    }


}