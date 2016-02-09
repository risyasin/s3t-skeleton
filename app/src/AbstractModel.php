<?php
/**
 * Created by PhpStorm.
 * User: yas
 * Date: 17/12/15
 * Time: 13:17
 */

namespace App;


use RedBeanPHP\R;
use RedBeanPHP\SimpleModel;
use RedBeanPHP\OODBBean;

/**
 * Class AbstractModel
 * @package App
 *
 * @property \App\AbstractModel $bean
 * @property string $created
 * @property string $updated
 *
 * @method open
 * @method dispense
 //* @method update
 * @method after_update
 * @method delete
 * @method after_delete
 *
 * @method import
 * @method export
 *
 */


class AbstractModel extends SimpleModel
{

    public $autoTime = true;

    /**
     * Tooling method.
     * @return string
     */
    private static function tableName()
    {
        // @Todo: implement a prefix support. if it's really needed. via xdispense method.
        $cls = explode('\\', strtolower(get_called_class()));
        return array_pop($cls);
    }


    // FUSE methods

    public function update()
    {
        if ($this->autoTime){
            if(empty($this->bean->created)){
                $this->bean->created = R::isoDateTime();
            }
            $this->bean->updated = R::isoDateTime();
        }
    }


    // non-dynamic methods. just wrap Redbean methods to save Model name //


    /**
     * Dispense self.
     *
     * @param int $num
     * @param bool $asArr
     * @return self
     */
    public static function create($num = 1, $asArr = false)
    {
        /* @var self $new */
        $new = R::dispense(self::tableName(), $num, $asArr);
        /* @var self $new */
        return $new;

    }



    /**
     * @param $bean
     * @return int|string
     */
    public static function save($bean)
    {
        try {
            return R::store($bean);
        } catch(\Exception $e) {
            return Base::discard($e);
        }
    }


    public static function paginate($limit = 20, $page = 1, $sql = null, $bindings = [])
    {
        if (!is_numeric($page) || $page < 1){ $page = 1; }

        $pd = (object) array(
            'limit'     => $limit,
            'page'      => $page,
            'offset'    => (($page-1)*$limit),
            'total'     => R::count(self::tableName(), $sql, $bindings),
            'pageCount' => 1,
            'rows'      => []
        );

        $pd->pageCount = ceil($pd->total/$limit);

        $sql = (is_null($sql)?'':$sql).' LIMIT '.$pd->offset.', '.$pd->limit;

        $pd->rows = R::findAll(self::tableName(), $sql, $bindings);

        return $pd;
    }


    /**
     * @param $id
     * @return OODBBean
     */
    public static function load($id)
    {
        try {
            return R::load(self::tableName(), $id);
        } catch(\Exception $e) {
            return Base::discard($e);
        }
    }


    /**
     * @param $id
     */
    public static function trash($id)
    {
        try {
            R::trash(self::tableName(), $id);
        } catch(\Exception $e) {
            Base::discard($e);
        }
    }




    /**
     * @return boolean
     */
    public static function wipe()
    {

        return R::wipe(self::tableName());

    }



    /**
     * @param $sql
     * @return int
     */
    public static function count($sql = null)
    {

        return R::count(self::tableName(), $sql);

    }


    /**
     * @param null $sql
     * @param array $bindings
     * @return array
     */
    public static function find($sql = null, $bindings = array())
    {

        return R::find(self::tableName(), $sql, $bindings);

    }


    /**
     * @param $sql
     * @param $bindings
     * @return OODBBean
     */
    public static function findOne($sql = null, $bindings = array())
    {

        return R::findOne(self::tableName(), $sql, $bindings);

    }


    /**
     * @param $sql
     * @param $bindings
     * @return array
     */
    public static function findAll($sql = null, $bindings = array())
    {

        return R::findAll(self::tableName(), $sql, $bindings);

    }


    /**
     * @param $sql
     * @param $bindings
     * @return \RedBeanPHP\BeanCollection
     */
    public static function collection($sql = null, $bindings = array())
    {

        return R::findCollection(self::tableName(), $sql, $bindings);

    }

    /**
     * @param $sql
     * @param $bindings
     * @return array
     */
    public static function findAndExport($sql = null, $bindings = array())
    {

        return R::findAndExport(self::tableName(), $sql, $bindings);

    }


    /**
     * @param $array
     * @return OODBBean
     */
    public static function findOrCreate($array)
    {

        return R::findOrCreate(self::tableName(), $array);

    }

    /**
     * @param array $like
     * @param string $sql
     * @return array
     */
    public static function findLike($like = array(), $sql = '')
    {

        return R::findLike(self::tableName(), $like, $sql);

    }

    /**
     * @param null $sql
     * @param array $bindings
     * @return OODBBean
     */
    public static function findLast($sql = NULL, $bindings = array())
    {

        return R::findLast(self::tableName(), $sql, $bindings);

    }

    /**
     * @param null $sql
     * @param array $bindings
     * @return array
     */
    public function findOrDispense($sql = NULL, $bindings = array())
    {

        return R::findOrDispense(self::tableName(), $sql, $bindings);

    }

}