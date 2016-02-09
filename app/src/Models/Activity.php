<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 09/02/16
 * Time: 00:43
 */

namespace App\Models;

use App\AbstractModel;

/**
 * Class Activity
 * @package App\Models
 *
 * @property int $id userId
 * @property string $type
 * @property string $message
 * @property array $data
 * @property int $time
 *
 * @property string $created redbean dt
 * @property string $updated redbean dt
 *
 * @property \App\Models\Activity $bean
 *
 */
class Activity extends AbstractModel
{

    public $autoTime = false;


    public function open()
    {
        $this->bean->data = json_decode($this->bean->data);
    }

    public function update()
    {
        $this->bean->data = json_encode($this->bean->data);
        $this->bean->time = time();
    }


    public static function add($type, $data)
    {
        // overload data
        if (is_string($data)){
            $data['message'] = $data;
        }

        /* @var self $new */
        $new = self::create();
        $new->type = $type;
        $new->message = $data['message'];
        $new->data = $data;

        self::save($new);

    }

}