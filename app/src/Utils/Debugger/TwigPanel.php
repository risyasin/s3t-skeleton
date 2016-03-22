<?php
/**
 * PHP version 7
 *
 * Created at 18/03/16 17:59 by yas
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */

namespace App\Utils\Debugger;

use App\Base;
use Tracy\IBarPanel;

/**
 * Class 
 *
 * @category Base
 * @package  TwigPanel
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class TwigPanel implements IBarPanel
{
    /**
     * Return bar label
     *
     * @return string
     */
    public function getTab()
    {

        $icon = 'data:image/png;base64,'.
        'iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAACYUlEQVQ4jaWS30uTURzGP+f9'.
        'uffs3Y/UmYrrxiKRICGFwoL+gCAJIrzoMv8Jr/1bvAhCuvMqCJKglkGZOiXNba7Nuek2987X'.
        'bacL24sL73rgwDnf8/DwPA9f+E8IgKWlpYzruqMXA4FCXXwA6hLx8r1Wr2fn5uaSBoDruqMH'.
        '+aNOyHLQhIY0JJYQ2EIjakjCusFBOsd5zcdvevzIpJl8+WgUwOha2dzIaY4ZIoQgYUVIGGGi'.
        'ZoS43UF4ZfrTHlQ6GEqncKAFEQIBS5dIM0xcMxk0IyTtPoZkDNotWn0h4jfiGLbOYbYCoeMg'.
        'TiBlmJKQLnFMF8sIIzSHk9I5raSLr9s0haRSNci1BX6sL+hI65bi2C6WHUY3XXwsdtJlyhFo'.
        'agLPBz8LheNTfss677a/Bw6CCHHbxrUcpCH59ekQyxfIegtyDc52qujeKXvOIalcgbzf6O1A'.
        'Af2WhWPqFHZKxK7p0GxRLRY5+ukRUxbHbpvaQJj8bosG570CAsiXimRSBSxfIe028YjkrNgi'.
        'Gnb5WDrAvx6l4nkcHmc488q9HSgEb96usLa7xunkII+fNbl/9z1DCZ+VySSDt9cZkV+5c8ul'.
        'cLpOpvKlZ6lYXl5W2xt7zWRy2IhGo2hAW3XodGlKdamc1KrsZ/ZbExPjodnZWWEAZPf3H4TC'.
        '2mixXKBYLgCQSn1+jRIgQKGYujf1vJvbdSX5fD4bOLgKi4uLSkqJArxGg4WFhSu5+j9vA5Dz'.
        '86+ezDyceTE9Nc34+DhSOgyPDH9b/bCaATp/T+8eXMbY2M3m1ubW063NrWCWGEh0ruL+AdOX'.
        '5uYOCtS5AAAAAElFTkSuQmCC';

        return '<span title="Twig Profiler"><img src="'.$icon.'"> Twig</span>';

    }


    /**
     * Panel html
     *
     * @return string
     */
    public function getPanel()
    {

        try {
            $twp = Base::$c['twigProfiler'];
        } catch(\Exception $e) {
            $twp = false;
        }

        if ($twp ?? false) {

            $dumper = new \Twig_Profiler_Dumper_Text();

            $report = explode("\n", $dumper->dump($twp));

            ob_start(); ?><div class="nette-inner">
            <ul style="margin: 0 5px; list-style-type: none;"><?php

                $vp = Base::$c['settings']['view']['templatePath'];

            foreach ($report as $ln) {
                if (strstr($ln, '.twig')) {
                    list($f, $c) = explode('.twig', $ln);
                    $fn = trim(str_replace('└', '', $f)).'.twig';
                    $f = str_replace(' ', '&nbsp;', $f).'.twig';
                    $url = 'editor://open/?file='.$vp.'/'.$fn.'&line=1';
                    $ln = '<a href="'.$url.'">'.$f.'</a>'.$c;
                }
                echo '<li>'.$ln.'</li>';
            } ?></ul></div><?php

            $var = ob_get_clean();

            return '<h3>Twig templates</h3>'.$var;

        } else {
            return 'No Twig template used!';
        }

    }



}