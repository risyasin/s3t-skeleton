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
 * @property \RedBeanPHP\OODBBean $bean Bean object
 *
 * @method open
 * @method dispense
 * @method update
 * @method after_update
 * @method delete
 * @method after_delete
 *
 */


class AbstractModel extends SimpleModel
{

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


    // non-dynamic methods. just wrap Redbean methods to save Model name //


    /**
     * @param int $num
     * @param bool $asArr
     * @return array|OODBBean
     */
    public static function create($num = 1, $asArr = false)
    {

        return R::dispense(self::tableName(), $num, $asArr);

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
            return Base::throw($e);
        }
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
            return Base::throw($e);
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
            Base::throw($e);
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

        return R::findLike(self::tableName(), $sql, $bindings);

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