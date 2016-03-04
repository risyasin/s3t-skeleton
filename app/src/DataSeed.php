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

namespace App;

use App\Models\Page;
use App\Models\User;
use RedBeanPHP\R;

/**
 * Class DataSeed
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 */
class DataSeed
{

    /**
     * Adds default user
     *
     * @return bool
     */
    public static function defaultUser()
    {

        /* @var \Slim\Http\Request $r */
        $r = Base::$c['request'];

        if (User::count() > 0) {
            // default user can only be added
            // when there is no valid user can be authenticated!
            $msg = 'Refusing to add default user. User table is not empty!';
            return Base::discard(new \Exception($msg));
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

        // @Todo: Consider this to move somewhere else,
        // as it's not a seeding functionality, when it's available.
        R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`mail`);');
        R::exec('ALTER TABLE `user` ADD UNIQUE INDEX (`user`);');

        return true;
    }


    /**
     * User list
     *
     * @return null
     */
    public static function users()
    {

        // no need to use users seeder.
        // on every login, auth module checks if user table exists

    }


    /**
     * Adds default page
     *
     * @return bool|int|string
     */
    public static function defaultPage()
    {
        // Page::wipe();
        if (count(Page::find('WHERE `path` = "/"')) == 0) {

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
            $msg = 'Refusing to add default page! '.
                'Page table already has a /default page.';
            return Base::discard(new \Exception($msg));
        }
    }

}