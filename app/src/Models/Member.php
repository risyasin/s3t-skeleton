<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 17/12/15
 * Time: 13:18
 */

namespace App\Models;

use App\AbstractModel;

/**
 * Class Member
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
 *
 * @property string $created redbean dt
 * @property string $updated redbean dt
 *
 * @property \App\Models\Member $bean
 */


class Member extends AbstractModel
{


}