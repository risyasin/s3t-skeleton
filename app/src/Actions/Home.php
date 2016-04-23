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

namespace App\Actions;

use App\Base;
use App\Origins\Action as AbstractAction;
use App\Models\User as User;
use App\Utils\Cache;
use App\Utils\Session;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Home
 *
 * @category Base
 * @package  App\Actions
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
final class Home extends AbstractAction
{

    /**
     * Home
     *
     * @param Request  $req  Req
     * @param Response $resp Resp
     * @param array    $args args
     *
     * @return null
     */
    public function index(Request $req, Response $resp, $args)
    {

        // $post = R::findOne('posts', 'id = ?', [4]);

        // $data['post'] = $post->getProperties();

        $data['users'] = User::findAll('ORDER BY name ASC LIMIT 5');
        // $data['nusers'] = R::findAll( 'fblg_user', 'ORDER BY name ASC LIMIT 5');

        // return Base::json($data);

        Cache::set('srv', $_SERVER);

        Cache::delete('last');
        $data['cl'] = Cache::via(
            'last',
            function () {
                return time();
            }
        );

        return Base::render('web/default.twig', $data);

    }

}
