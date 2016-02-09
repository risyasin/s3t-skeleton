<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 17/12/15
 * Time: 01:17
 */

namespace App\Modules;

use App\Base;
use App\AbstractModule;
use App\DataSeed;
use App\Models\User;
use App\Models\Page;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;


class Admin extends AbstractModule
{

    public $requires = ['db', 'auth'];

    private $pathPrefix = '/admin';


    /**
     * @param $app \Slim\App
     */
    public function routes($app)
    {

        $app->group($this->pathPrefix, function () use ($app) {

            // GET /admin/home
            $app->get('/home', function (){

                Base::log('test 3 - admin home');

                Base::set('server', $_SERVER);

                Base::set('server', User::findAll());

                DataSeed::defaultUser();

                return Base::render('modules/admin/home.twig');

            })->setName('admin.home');


            // GET /admin/pages
            $app->get('/pages/{p}', function ($req, $res, $args){

                $pages = Page::paginate(10, $args['p'], 'ORDER BY `path` ASC');

                return Base::render('modules/admin/pages.twig', compact('pages'));

            })->setName('admin.pages');

            // GET /admin/page
            $app->get('/page/{id}', function ($req, $resp, $args){

                $page = 'new';

                if ($args != 'new'){
                    $page = Page::load($args['id']);
                }

                return Base::render('modules/admin/pageform.twig', compact('page'));

            })->setName('admin.page');

            // GET /admin/menus
            $app->get('/menus', function (){

                Base::log('test 3 - admin menus');
                Base::log(Base::get('login'));


                return Base::render('modules/admin/menu.twig');

            })->setName('admin.menus');

            // GET /admin/menus
            $app->get('/templates', function (ServerRequestInterface $request, ResponseInterface $response){

                Base::log('test 3 - admin templates');
                Base::log(Base::get('login'));


                return Base::render('modules/admin/templates.twig');

            })->setName('admin.templates');

            // GET /admin/users
            $app->get('/users', function (ServerRequestInterface $request, ResponseInterface $response){

                Base::log('test 3 - admin home');
                Base::log(Base::get('login'));


                return Base::render('modules/admin/users.twig');

            })->setName('admin.users');


            // GET /admin/roles
            $app->get('/roles', function (ServerRequestInterface $request, ResponseInterface $response){

                Base::log('test 3 - admin roles');
                Base::log(Base::get('login'));


                return Base::render('modules/admin/roles.twig');

            })->setName('admin.roles');


            // GET /admin/roles
            $app->get('/infophp', function (ServerRequestInterface $request, ResponseInterface $response){

                echo phpinfo();

            })->setName('admin.infophp');


        })->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {

            // Admin requires auth login for any access!
            if (empty($_SESSION['login']) || $_SESSION['login'] < 0){
                return Base::redirect(Base::pathFor('auth.login'));
            }

            $response = $next($request, $response);

            return $response;
        });

    }




}