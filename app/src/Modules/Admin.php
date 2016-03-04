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

namespace App\Modules;

use App\Base;
use App\AbstractModule;
use App\Models\Page;
use Slim\Http\Request;
use Slim\Http\Response;


/**
 * Class Admin
 *
 * @category Base
 * @package  App\Modules
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Admin extends AbstractModule
{
    /* @var array $requires requirements of module */
    public $requires = ['db', 'auth'];

    /* @var string $pathPrefix */
    public $pathPrefix = '/admin';


    /**
     * Module registry
     *
     * @return bool
     */
    public function register()
    {
        return true;
    }

    /**
     * Route definitions
     *
     * @param \Slim\App $app Slim
     *
     * @return Response
     */
    public function routes($app)
    {
        $app->group(
            $this->pathPrefix,
            function () use ($app) {
                // GET /admin/home
                $app->get(
                    '/home',
                    function () {
                        return Base::render('modules/admin/home.twig');
                    }
                )->setName('admin.home');

                // GET /admin/pages
                $app->get(
                    '/pages/{p}',
                    function (Request $req, Response $res, $args) {

                        $pages = Page::paginate(
                            10,
                            $args['p'],
                            'ORDER BY `path` ASC'
                        );

                        return Base::render(
                            'modules/admin/pages.twig',
                            compact('pages')
                        );

                    }
                )->setName('admin.pages');

                // GET /admin/page
                $app->get(
                    '/page/{id}',
                    function (Request $req, Response $resp, $args) {

                        if ($args['id'] != 'new') {
                            $page = Page::load($args['id']);
                        } else {
                            $page = Page::create();
                        }

                        return Base::render(
                            'modules/admin/pageform.twig',
                            compact('page')
                        );

                    }
                )->setName('admin.page');


                // GET /admin/menus
                $app->get(
                    '/menus',
                    function (Request $req, Response $resp, $args) {

                        Base::log('test 3 - admin menus');
                        Base::log(Base::get('login'));

                        return Base::render('modules/admin/menu.twig');

                    }
                )->setName('admin.menus');

                // GET /admin/menus
                $app->get(
                    '/templates',
                    function (Request $req, Response $resp, $args) {

                        Base::log('test 3 - admin templates');
                        Base::log(Base::get('login'));

                        return Base::render('modules/admin/templates.twig');

                    }
                )->setName('admin.templates');

                // GET /admin/users
                $app->get(
                    '/users',
                    function (Request $req, Response $resp, $args) {

                        Base::log('test 3 - admin home');
                        Base::log(Base::get('login'));

                        return Base::render('modules/admin/users.twig');

                    }
                )->setName('admin.users');


                // GET /admin/roles
                $app->get(
                    '/roles',
                    function (Request $req, Response $resp, $args) {

                        Base::log('test 3 - admin roles');
                        Base::log(Base::get('login'));

                        return Base::render('modules/admin/roles.twig');

                    }
                )->setName('admin.roles');


                // GET /admin/roles
                $app->get(
                    '/phpinfo',
                    function (Request $req, Response $resp, $args) {
                        // phpinfo
                        echo phpinfo();
                    }
                )->setName('admin.phpinfo');

            }
        )->add( // Middleware
            function (Request $request, Response $response, $next) {

                // Admin requires auth login for any access!
                if (empty($_SESSION['login']) || $_SESSION['login'] < 0) {
                    return Base::redirect('auth.login');
                }

                $response = $next($request, $response);

                return $response;
            }
        );
    }

}