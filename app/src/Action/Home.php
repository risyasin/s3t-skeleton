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

namespace App\Action;

use App\Base;
use App\AbstractAction;
use App\DataSeed;
use App\Models\User as User;
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

        Base::dump($_SERVER);

        return Base::render('web/default.twig', $data);

    }

}
