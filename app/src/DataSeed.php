<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 12/01/16
 * Time: 00:30
 */

namespace App;

use App\Models\Page;
use App\Models\User;
use RedBeanPHP\R as R;

class DataSeed
{

    public static function defaultUser()
    {

        if ($d = Base::$c['auth']['defaultUser'] ?? false){

            /* @var $r \Psr\Http\Message\ServerRequestInterface  */
            $r = Base::$c['request'];

            if (User::count() > 12){
                // default user can only be added when there is no valid user can be authenticated!
                return Base::throw(new \Exception('Refusing to add default user. User table is not empty!'));
            }

            /* @var \App\Models\User $defUser */
            $defUser = User::create();

            $defUser->user = $d[0];
            $defUser->password = md5($d[1]);
            $defUser->name = $d[2];
            $defUser->mail = $d[3];
            $defUser->role = 'admin';
            $defUser->ip = ($r->getServerParams()['REMOTE_ADDR'] ?? '127.0.0.1');

            User::save($defUser);

            // @Todo: Consider this to move somewhere else, as it's not a seeding functionality, when it's available.
            R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`mail`);');
            R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`user`);');

        }

        return true;
    }

    public static function users()
    {

        // no need to use users seeder.
        // on every login, auth module checks if user table exists

    }


    public static function pages()
    {

        /* @var \App\Models\Page $page */
        $page = Page::create();

        $page->name = 'Default page';
        $page->title = 'Default page of skeleton app';
        $page->keywords = 'Default page of skeleton app';

    }

}