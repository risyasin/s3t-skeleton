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
use RedBeanPHP\R;

class DataSeed
{

    public static function defaultUser()
    {

        /* @var $r \Psr\Http\Message\ServerRequestInterface  */
        $r = Base::$c['request'];

        if (User::count() > 0){
            // default user can only be added when there is no valid user can be authenticated!
            return Base::discard(new \Exception('Refusing to add default user. User table is not empty!'));
        }

        /* @var \App\Models\User $defUser */
        $defUser = User::create();

        $defUser->user = 'admin';
        $defUser->password = 'pass';
        $defUser->name = 'Default Admin';
        $defUser->mail = 'root@localhost';
        $defUser->role = 'admin';
        $defUser->ip = '127.0.0.1';

        User::save($defUser);

        // @Todo: Consider this to move somewhere else, as it's not a seeding functionality, when it's available.
        R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`mail`);');
        R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`user`);');

        return true;
    }

    public static function users()
    {

        // no need to use users seeder.
        // on every login, auth module checks if user table exists

    }


    public static function defaultPage()
    {
        // Page::wipe();
        if (count(Page::find('WHERE `path` = "/"')) == 0){

            R::exec('ALTER TABLE `page` ADD UNIQUE INDEX (`path`);');

            /* @var \App\Models\Page $page */
            $page = Page::create();
            $page->name = 'Default page';
            $page->path = '/';
            $page->template = 'default.twig';
            $page->title = 'Default page of skeleton app';
            $page->keywords = 'skeleton app, sample, keywords, page maker, seo';
            $page->content = Tools::generateText(1200, true);
            $page->lang = Base::$locale;

            return Page::save($page);

        } else {
            return Base::discard(new \Exception('Refusing to add default page! Page table already has a /default page.'));
        }

    }


}