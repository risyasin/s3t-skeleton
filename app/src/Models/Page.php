<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 13/01/16
 * Time: 12:54
 */

namespace App\Models;


use App\AbstractModel;
use App\Tools;

/**
 * Class Page
 *
 * @package App\Models
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
 *
 */


class Page extends AbstractModel
{

    public function update()
    {

        if (empty($this->bean->path) || trim($this->bean->path) == ''){
            $this->bean->path = $this->bean->title;
        }

        $this->bean->path = Tools::slugify($this->bean->path);

        $this->bean->path = '/'.ltrim($this->bean->path, '/');

    }



}