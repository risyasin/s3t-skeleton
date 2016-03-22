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
 * Class Activity
 *
 * @category Base
 * @package  App\Models
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
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
 */
class Activity extends AbstractModel
{

    public $autoTime = false;

    /**
     * FUSE method open via Redbean
     *
     * @return null
     */
    public function open()
    {
        $this->bean->data = json_decode($this->bean->data);
    }

    /**
     * FUSE Method update via Redbean
     *
     * @return null
     */
    public function update()
    {
        $this->bean->data = json_encode($this->bean->data);
        $this->bean->time = time();
    }


    /**
     * Add new activity simple elegant
     *
     * @param string $type Activity type
     * @param mixed  $data Any date for activity, will be wrapped into an array
     *
     * @return null
     */
    public static function add($type, $data)
    {
        // wrap as array
        if (is_string($data)) {
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