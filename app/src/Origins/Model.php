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

namespace App\Origins;


use RedBeanPHP\R as R;
use RedBeanPHP\SimpleModel;
use RedBeanPHP\OODBBean;

/**
 * Class Origin Model
 *
 * @category Base
 * @package  App
 * @author   Yasin inat <risyasin@gmail.com>
 * @license  Apache 2.0
 * @link     https://www.evrima.net/slim3base
 *
 * @property \App\Origins\Model $bean
 * @property string $created
 * @property string $updated
 *
 * @method open
 * @method dispense
 * @method after_update
 * @method delete
 * @method after_delete
 *
 * @method import
 * @method export
 *
 * -- OODBBean Methods
 *
 * @method setBeanHelper
 * @method getIterator
 * @method importRow
 * @method importFrom
 * @method inject
 * @method getID
 * @method all
 * @method alias
 * @method getProperties
 * @method getPropertiesAndType
 * @method clearModifiers
 * @method setProperty
 * @method getMeta
 * @method moveMeta
 * @method setMeta
 * @method copyMetaFrom
 * @method fetchAs
 * @method poly
 * @method traverse
 * @method isEmpty
 * @method setAttr
 * @method unsetAll
 * @method isTainted
 * @method hasChanged
 * @method hasListChanged
 */
abstract class Model extends SimpleModel
{

    /* @var int Pagination default row count */
    const ROW_LIMIT = 20;

    /* @var bool $autoTime Datetime records toggle */
    public $autoTime = true;


    /**
     * Tooling method
     *
     * @return string
     */
    private static function _table()
    {
        // @Todo: implement a prefix support.
        // if it's really needed. via xdispense method.
        $cls = explode('\\', strtolower(get_called_class()));
        return array_pop($cls);
    }


    // ------------------  FUSE methods ------------------ //

    /**
     * FUSE Updater for dt fields.
     *
     * @return null
     */
    public function update()
    {
        if ($this->autoTime) {
            if (empty($this->bean->created)) {
                $this->bean->created = R::isoDateTime();
            }

            $this->bean->updated = R::isoDateTime();
        }
    }


    // non-dynamic methods. just wrap Redbean methods to save Model name //


    /**
     * Dispense self.
     *
     * @param int  $num   How many
     * @param bool $asArr As array ?
     *
     * @return self
     */
    public static function create($num = 1, $asArr = false)
    {
        /* @var self $new */
        $new = R::dispense(self::_table(), $num, $asArr);
        return $new;

    }


    /**
     * Save bean
     *
     * @param OODBBean|AbstractModel $bean Bean
     *
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


    /**
     * Simple Pagination
     *
     * @param int   $limit Limit
     * @param int   $page  Which page?
     * @param null  $sql   Any Sql filter
     * @param array $bind  Bindings
     *
     * @return object
     */
    public static function paginate(
        $limit = self::ROW_LIMIT,
        $page = 1,
        $sql = null,
        $bind = []
    ) {

        if (!is_numeric($page) || $page < 1) {
            $page = 1;
        }

        $pd = (object) [
            'limit'     => $limit,
            'page'      => $page,
            'offset'    => (($page-1) * $limit),
            'total'     => R::count(self::_table(), $sql, $bind),
            'pageCount' => 1,
            'rows'      => []
        ];

        $pd->pageCount = ceil($pd->total / $limit);

        $sql = (is_null($sql)?'':$sql).' LIMIT '.$pd->offset.', '.$pd->limit;

        $pd->rows = R::findAll(self::_table(), $sql, $bind);

        return $pd;
    }


    /**
     * Loader by ID
     *
     * @param int $id ID to load
     *
     * @return bool|OODBBean
     */
    public static function load($id)
    {
        // @TODO: Do not catch it here, just pass
        try {
            return R::load(self::_table(), $id);
        } catch(\Exception $e) {
            return Base::discard($e);
        }
    }


    /**
     * Trash it
     *
     * @param int $id ID to delete
     *
     * @return null
     */
    public static function trash($id)
    {
        // @TODO: Do not catch it here, just pass
        try {
            R::trash(self::_table(), $id);
        } catch(\Exception $e) {
            Base::discard($e);
        }
    }


    /**
     * Wipe table
     *
     * @return bool
     */
    public static function wipe()
    {

        return R::wipe(self::_table());

    }


    /**
     * Count rows
     *
     * @param string $sql SQL filter
     *
     * @return int
     */
    public static function count($sql = null)
    {

        return R::count(self::_table(), $sql);

    }


    /**
     * Simple Rows Finder
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bind params
     *
     * @return array
     */
    public static function find($sql = null, $bind = [])
    {

        return R::find(self::_table(), $sql, $bind);

    }


    /**
     * Simple Row Finder
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return OODBBean
     */
    public static function findOne($sql = null, $bind = [])
    {

        return R::findOne(self::_table(), $sql, $bind);

    }


    /**
     * All Finder
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return array
     */
    public static function findAll($sql = null, $bind = [])
    {

        return R::findAll(self::_table(), $sql, $bind);

    }


    /**
     * RedBean Collection
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return \RedBeanPHP\BeanCollection
     */
    public static function collection($sql = null, $bind = [])
    {

        return R::findCollection(self::_table(), $sql, $bind);

    }

    /**
     * Finds & Exports, Regular rows
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return array
     */
    public static function findAndExport($sql = null, $bind = [])
    {

        return R::findAndExport(self::_table(), $sql, $bind);

    }


    /**
     * Finds if exists or creates
     *
     * @param array $array Find Data
     *
     * @return OODBBean
     */
    public static function findOrCreate($array)
    {

        return R::findOrCreate(self::_table(), $array);

    }

    /**
     * Find Like
     *
     * @param array  $like Similar data
     * @param string $sql  SQL Filter
     *
     * @return array
     */
    public static function findLike($like = [], $sql = '')
    {

        return R::findLike(self::_table(), $like, $sql);

    }

    /**
     * Finds the last
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return OODBBean
     */
    public static function findLast($sql = null, $bind = [])
    {

        return R::findLast(self::_table(), $sql, $bind);

    }

    /**
     * Finds or Dispenses
     *
     * @param string $sql  SQL Filter
     * @param array  $bind Bindings
     *
     * @return array
     */
    public function findOrDispense($sql = null, $bind = [])
    {

        return R::findOrDispense(self::_table(), $sql, $bind);

    }


}