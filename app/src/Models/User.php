<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 17/12/15
 * Time: 13:18
 */

namespace App\Models;


use App\AbstractModel;
use App\Tools;

/**
 * Class User
 *
 * @package App\Models
 *
 * @property int $id userId
 * @property string $user actually username
 * @property string $mail mail address
 * @property string $name full name
 * @property string $password // should be in md5(password) form
 * @property string $role possible roles admin, editor, read etc.
 * @property string $ip last ip address of user.
 * @property string $status Status
 * @property string $created redbean dt
 * @property string $updated redbean dt
 *
 * @method array customers
 * @property \App\Models\User bean
 *
 */


class User extends AbstractModel
{

    // ------ Custom methods ------- //

    /**
     * User Authentication func.
     *
     * @param string $userOrMail auth can be done via username or mail or id.
     * @param string $pass plain password text
     * @return object
     */
    public static function authenticate($userOrMail = '', $pass = '')
    {

        if (trim($userOrMail) == '' || $pass == ''){
            return (object) ['result' => false, 'error' => 'missing_value'];
        }

        /** @var \App\Models\User $u */
        $u = self::findOne('`mail` = :u or `username` = :u or `id` = :u', ['u' => $userOrMail]);

        if ($u == null){
            return (object) ['result' => false, 'error' => 'no_such_user', 'user' => $userOrMail];
        }

        if (password_verify((string) $pass, $u->password) == false) {
            return (object) ['result' => false, 'error' => 'wrong_pass'];
        }

        // report if user is disabled, only after authenticated.
        if ($u->status == 'D'){
            return (object) ['result' => false, 'error' => 'user_disabled'];
        }

        return (object) ['result' => true, 'user' => $u->export()];

    }



    /**
     * Password change func.
     *
     * @param int $id user id
     * @param string $newpass new password - plain text
     * @param bool $oldpass - old password plain text. optional
     * @return array
     */
    public static function changePass($id, $newpass, $oldpass = false)
    {
        /* @var \App\Models\User $u */
        $u = User::load($id);

        if ($oldpass != false && !password_verify($oldpass, $u->password)){
            return (object) ['success' => false, 'error' => 'wrong_pass'];
        }

        $u->password = password_hash($newpass, PASSWORD_BCRYPT);

        self::save($u);

        return (object) ['success' => true];
    }

    // ------ FUSE methods ------- //


    /**
     * Fuse Method. Runs on every update
     *
     */
    public function update()
    {

        // if it's not already hashed!
        if (!(substr($this->bean->password, 0, 4) == '$2y$')){
            // Do not update pass directly. use changePass method
            $this->bean->password = password_hash((string) $this->bean->password, PASSWORD_BCRYPT);
        }

        $this->bean->ip = (!empty($_SERVER['REMOTE_ADDR'])?$_SERVER['REMOTE_ADDR']:'127.0.0.1');

        $this->bean->updated = Tools::isoDateTime();

    }

}