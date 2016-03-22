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

/**
 * Class Member
 *
 * @category Base
 * @package  App\Models
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 *
 * @property int $id userId
 * @property string $user actually username
 * @property string $mail mail address
 * @property string $name full name
 * @property string $password // should be in md5(password) form
 * @property string $role possible roles admin, editor, read etc.
 * @property string $ip last ip address of user.
 *
 * @property string $created redbean dt
 * @property string $updated redbean dt
 *
 * @property \App\Models\Member $bean
 */


class Member extends AbstractModel
{


}