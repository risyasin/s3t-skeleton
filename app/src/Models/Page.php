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
 * Class Page
 *
 * @category Base
 * @package  App\Models
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 *
 * @property int $id
 * @property string $lang
 * @property string $path
 * @property string $name page name
 * @property string $content
 * @property string $title
 * @property string $keywords
 * @property string $template
 * @property string $data
 * @property string $description
 *
 * @property string $created redbean dt
 * @property string $updated redbean dt
 *
 * @property \App\Models\Page $bean
 */


class Page extends AbstractModel
{

    /**
     * Fuse method for updates
     *
     * @return null
     */
    public function update()
    {

        if (empty($this->bean->path) || trim($this->bean->path) == '') {
            $this->bean->path = $this->bean->title;
        }

        $this->bean->path = Tools::slugify($this->bean->path);

        $this->bean->path = '/'.ltrim($this->bean->path, '/');

    }



}