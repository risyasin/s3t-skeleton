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

namespace App\Models;


use App\Origins\Model as AbstractModel;
use App\Tools;

/**
 * Class User
 *
 * @category Base
 * @package  App\Models
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 *
 * @property int    $id       UserId
 * @property string $user     Actually username
 * @property string $mail     Mail address
 * @property string $name     Full name
 * @property string $password Should be in md5(password) form
 * @property string $role     Possible roles admin, editor, read etc.
 * @property string $ip       Last ip address of user.
 * @property string $status   Status
 * @property string $created  Redbean dt
 * @property string $updated  Redbean dt
 * @property User   $bean
 *
 * @method array customers
 */

class User extends AbstractModel
{

    // ------ Custom methods ------- //

    /**
     * User Authentication func.
     *
     * @param string $userOrMail Auth can be done via username or mail or id.
     * @param string $pass       Plain password text
     *
     * @return object
     */
    public static function authenticate($userOrMail = '', $pass = '')
    {

        if (trim($userOrMail) == '' || $pass == '') {
            return (object) ['result' => false, 'error' => 'missing_value'];
        }

        /* @var \App\Models\User $u */
        $u = self::findOne(
            '`mail` = :u or `username` = :u or `id` = :u',
            ['u' => $userOrMail]
        );

        if ($u == null) {
            return (object) [
                'result' => false,
                'error'  => 'no_such_user',
                'user'   => $userOrMail
            ];
        }

        if (password_verify((string) $pass, $u->password) == false) {

            return (object) ['result' => false, 'error' => 'wrong_pass'];

        }

        // report if user is disabled, only after authenticated.
        if ($u->status == 'D') {
            return (object) [
                'result' => false,
                'error'  => 'user_disabled'
            ];
        }

        return (object) ['result' => true, 'user' => $u->export()];

    }



    /**
     * Password change func.
     *
     * @param int    $id      User id
     * @param string $newPass New password - plain text
     * @param bool   $oldPass Old password plain text. optional
     *
     * @return array
     */
    public static function changePass($id, $newPass, $oldPass = false)
    {
        /* @var \App\Models\User $u */
        $u = User::load($id);

        if ($oldPass != false && !password_verify($oldPass, $u->password)) {
            return (object) [
                'success' => false,
                'error' => 'wrong_pass'
            ];
        }

        $u->password = password_hash($newPass, PASSWORD_BCRYPT);

        self::save($u);

        return (object) ['success' => true];
    }

    /**
     * Fuse Method. Runs on every update
     *
     * @return null
     */
    public function update()
    {

        // if it's not already hashed!
        if (!(substr($this->bean->password, 0, 4) == '$2y$')) {
            // Do not update pass directly. use changePass method
            $hash = password_hash((string) $this->bean->password, PASSWORD_BCRYPT);
            $this->bean->password = $hash;
        }

        $this->bean->ip = $_SERVER['REMOTE_ADDR']??'127.0.0.1';

        $this->bean->updated = Tools::isoDateTime();

    }

}