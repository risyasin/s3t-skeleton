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
//use App\Utils\Seeder\Json as DataSeed;
use App\Models\Activity;
use App\Models\User;
use App\Origins\Module as AbstractModule;
use App\Utils\Session;
use Slim\App;
use Slim\Csrf\Guard;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class Auth
 *
 * @category Base
 * @package  App\Modules
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class Auth extends AbstractModule
{
    /* @var array $requires requirements of module */
    public $requires = ['db', 'session'];

    /* @var string $pathPrefix */
    public $pathPrefix = '/auth';


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
     * Route definitions of module
     *
     * @param App $app Slim App
     *
     * @return null
     */
    public function routes($app)
    {

        $app->group(
            $this->pathPrefix,
            function () use ($app) {

                // GET Login
                $app->get(
                    '/login',
                    function () {
                        /* @var \Slim\Container $this */

                        if (Session::get('login')) {
                            Base::redirect(
                                Base::$c['auth']['successRoute']
                            );
                        }

                        // @TODO: Implement here a nice remember me!

                        // if you want to use CSRF
                        $csrf_name = Base::$request->getAttribute('csrf_name');
                        $csrf_value = Base::$request->getAttribute('csrf_value');

                        Base::render(
                            'modules/auth/login.twig',
                            compact('csrf_name', 'csrf_value')
                        );

                    }
                )->setName('auth.login');

                // POST Login
                $app->post(
                    '/login',
                    function () {

                        $sql = '(user = :user or mail = :user) and password = :pass';

                        if (false === Base::$request->getAttribute('csrf_result')) {
                            Base::set('error', 'form.submission');
                            // exit immediately
                            Base::render('modules/auth/login.twig');
                        }

                        $f = (object) Base::$request->getParsedBody();

                        if (strlen($f->user) > 1) {

                            $user = User::findOne(
                                $sql,
                                ['user' => $f->user, 'pass' => $f->pass]
                            );

                            if ($user['id'] ?? false) {

                                Session::put('login', (int) $user['id']);

                                $_SESSION['user'] = $login = (object) [
                                    'name' => $user['name'],
                                    'mail' => $user['mail'],
                                    'ts' => time(),
                                    'id' => (int) $user['id']
                                ];

                                Activity::add(
                                    'login',
                                    ['message' => $user['name'].' logged_in']
                                );

                                Session::delete('wrongpass');

                                if ($f->rememberme ?? false) {
                                    Base::setCookie('rm', md5($user['mail']));
                                }

                                Base::redirect(
                                    Base::$c['auth']['successRoute']
                                );

                            } else {
                                Session::increment('wrongpass');
                                Base::set('error', 'wrong.password');
                            }

                            Base::render('modules/auth/login.twig');
                        } else {
                            Base::set('error', 'missing.username');
                        }

                        Base::render('modules/auth/login.twig');
                    }
                );



                // GET Logout
                $app->get(
                    '/profile',
                    function () {

                        Base::render('modules/auth/profile.twig');

                    }
                )->setName('auth.profile');


                // GET Logout
                $app->get(
                    '/logout',
                    function () {

                        Session::delete('login');
                        Session::delete('user');

                        Base::redirect('auth.login');

                    }
                )->setName('auth.logout');

            }
        );

        return null;
    }


    /**
     * Module PostApp State
     *
     * @param App $app Slim app
     *
     * @return null
     */
    public function postApp($app)
    {

        $guard = new Guard();

        $guard->setFailureCallable(
            function (Request $req, Response $resp, $next) {
                $request = $req->withAttribute('csrf_result', 'FAILED');
                return $next($request, $resp);
            }
        );

        $app->add($guard);

        return null;
    }

}