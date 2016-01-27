<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 17/12/15
 * Time: 03:38
 */

namespace App\Modules;

use App\Base;
use App\DataSeed;
use App\Models\User;
use App\AbstractModule;
use Slim\Csrf\Guard;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;



class Auth extends AbstractModule
{

    public $requires = ['db', 'session'];

    private $pathPrefix = '/auth';



    public function register()
    {

        return true;
    }


    /**
     * @param $app \Slim\App
     * @return mixed
     */
    public function routes($app)
    {

        if (($_SESSION['login'] ?? false) && ($_SESSION['user']->id > 0)){
            Base::set('login', $_SESSION['user']);
        } else {
            Base::set('login', false);
        }

        $app->group($this->pathPrefix, function () use ($app) {

            // GET Login
            $app->get('/login', function () {
                /* @var $this \Slim\Container  */

                if (($_SESSION['login'] ?? false) && ($_SESSION['user']->id > 0)){
                    return Base::redirect(Base::pathFor(Base::$c['auth']['successPath']));
                }

                // if no user exist in db, then create default,
                // so can't lock ourselves outside
                if (User::count() == 0){
                    DataSeed::defaultUser();
                }

                // @TODO: Implement here a nice remember me!

                // if you want to use CSRF
                $data['csrf_name'] = Base::$request->getAttribute('csrf_name');
                $data['csrf_value'] = Base::$request->getAttribute('csrf_value');

                return Base::render('modules/auth/login.twig', $data);

            })->setName('auth.login');


            // POST Login
            $app->post('/login', function () {

                if (false === Base::$request->getAttribute('csrf_result')) {

                    Base::set('error', 'form.submission');

                } else {

                    $f = (object) Base::$request->getParsedBody();

                    if (strlen($f->user) > 1){

                        $qp = ['user' => $f->user, 'pass' => $f->pass];
                        $user = User::findOne('(user = :user or mail = :user) and password = :pass', $qp);

                        if ($user['id'] ?? false){

                            $_SESSION['login'] = (int) $user['id'];

                            $_SESSION['user'] = $login = (object) [
                                'name' => $user['name'],
                                'mail' => $user['mail'],
                                'ts' => time(),
                                'id' => (int) $user['id']
                            ];

                            unset($_SESSION['wrongpass']);

                            if ($f->rememberme ?? false){
                                Base::setCookie('rm', md5($user['mail']));
                            }

                            return Base::redirect(Base::pathFor(Base::$c['auth']['successPath']));

                        } else {

                            if (!$_SESSION['wrongpass']){ $_SESSION['wrongpass'] = 0; }
                            $_SESSION['wrongpass'] += 1;
                            Base::set('error', 'wrong.password');

                        }

                        return Base::render('modules/auth/login.twig');

                    } else {

                        Base::set('error', 'missing.username');

                    }

                }

                return Base::render('modules/auth/login.twig');
            });



            // GET Logout
            $app->get('/profile', function () {
                /* @var $this \Slim\Container  */

                return Base::render('modules/auth/profile.twig');

            })->setName('auth.profile');


            // GET Logout
            $app->get('/logout', function () {
                /* @var $this \Slim\Container  */

                unset($_SESSION['login']);
                unset($_SESSION['user']);

                return Base::redirect($this->router->pathFor('auth.login'));

            })->setName('auth.logout');


        })->add(function (ServerRequestInterface $request, ResponseInterface $response, $next) {

            $response = $next($request, $response);

            return $response;
        });

    }


    /**
     * @param $app \Slim\App
     */
    public function postApp($app)
    {

         $guard = new Guard();
         $guard->setFailureCallable(function (ServerRequestInterface $request, ResponseInterface $response, $next) {
            $request = $request->withAttribute('csrf_result', 'FAILED');
            return $next($request, $response);
         });
         $app->add($guard);

    }


}