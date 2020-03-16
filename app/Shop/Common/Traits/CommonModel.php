<?php

namespace App\Shop\Common\Traits;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CommonModel
{
    use SoftDeletes;

    protected $setPrimaryValue = null;
    protected $attributes = [];
    // protected $dates = ['deleted_at'];
    // public $exists = true;
    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    // public function __construct()
    // {
    //     $this->timestamps = true;
    //     $this->__set('created_by', 1);
    //     $this->__set('updated_by', 1);
    // }
    public static function setCommonDefault($values, $flag = 'insert')
    {
        if (is_object($values)) {
            $values = (array) $values;
        }
        $user_id = 1;
        switch ($flag) {
            case 'update': {
                    $values['updated_by'] = $user_id;
                    $values['updated_at'] = now();
                    break;
                }
            case 'delete': {
                    $values['deleted_at'] = now();
                    break;
                }
            default: {
                    $values['created_by'] = $values['updated_by'] = $user_id;
                    $values['created_at'] = $values['updated_at'] = now();
                    break;
                }
        }
        return $values;
    }

    public static function createdAtColumn()
    {
        return 'created_at';
    }
    public static function  updatedAtColumn()
    {
        return 'updated_at';
    }
    public static function  createdByColumn()
    {
        return 'created_by';
    }
    public static function  updatedByColumn()
    {
        return 'updated_by';
    }

    public function getTableColumnCreatedAt()
    {
        return $this->getTable() . $this->createdAtColumn();
    }
    public function getTableColumnUpdatedAt()
    {
        return $this->getTable() . $this->updatedAtColumn();
    }
    public function getTableColumnCreatedBy()
    {
        return $this->getTable() . $this->createdByColumn();
    }
    public function getTableColumnUpdatedBy()
    {
        return $this->getTable() . $this->updatedByColumn();
    }

    public function insertByCommon($values)
    {
        if (count($values) != count($values, COUNT_RECURSIVE)) {
            for ($i = 0; $i < count($values); $i++) {
                $values[$i] = $this->setCommonDefault($values[$i]);
            }
        } else {
            $values = $this->setCommonDefault($values);
        }
        return $this->insert($values);
    }
    public function createByCommon($values)
    {
        $values = $this->setCommonDefault($values);
        return $this->create($values);
    }
    public function insertGetIdByCommon($values)
    {
        if (count($values) != count($values, COUNT_RECURSIVE)) {
            for ($i = 0; $i < count($values); $i++) {
                $values[$i] = $this->setCommonDefault($values[$i]);
            }
        } else {
            $values = $this->setCommonDefault($values);
        }
        return $this->insertGetId($values, $this->primaryKey);
    }

    public function updateByCommon(array $where, $values)
    {
        if (count($values) != count($values, COUNT_RECURSIVE)) {
            for ($i = 0; $i < count($values); $i++) {
                $values[$i] = $this->setCommonDefault($values[$i], 'update');
            }
        } else {
            $values = $this->setCommonDefault($values, 'update');
        }
        return $this->where($where)->update($values);
    }

    public function setParamByColumn(array $values)
    {
        $columns = Schema::getColumnListing($this->getTable());
        $attributes = array();
        foreach ($columns as $key => $value) {
            if (array_search($value, $values)) {
                $attributes[$value] = $values[$value];
            }
        }
        return $attributes;
    }
    // public function delete(array $where){
    //     foreach ($where as $key => $val) {
    //         $this->where($val[0], $val[1], $val[2]);
    //     }
    // }

    // public function getTable()
    // {
    //     return $this->table;
    // }

    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }
    public function getTablePrimaryKey($as = null)
    {
        return $this->getTable() . '.' . $this->primaryKey . (empty($as) ? '' : ' as ' . $as);
    }

    public function getTableColumn($key = null)
    {
        if (empty($key))
            return $this->getTable() . '.*';
        else
            return $this->getTable() . '.' . $key;
    }
    public function getTableColumnAs($key, $as)
    {
        return $this->getTable() . '.' . $key . ' as ' . $as;
    }

    public function getTableColumnDelete()
    {
        return $this->getTable() . '.' . $this->getDeletedAtColumn();
    }

    /**
     * Destroy the models for the given IDs.
     *
     * @param  \Illuminate\Support\Collection|array|int  $ids
     * @return int
     */
    public static function destroyWithColumn($ids, $key)
    {
        // We'll initialize a count here so we will return the total number of deletes
        // for the operation. The developers can then check this number as a boolean
        // type value or get this total count of records deleted for logging, etc.
        $count = 0;

        if ($ids instanceof BaseCollection) {
            $ids = $ids->all();
        }

        $ids = is_array($ids) ? $ids : [$ids];

        // We will actually pull the models from the database table and call delete on
        // each of them individually so that their events get fired properly with a
        // correct set of attributes in case the developers wants to check these.
        ($instance = new static)->getKeyName();

        foreach ($instance->whereIn($key, $ids)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }

    public function setPrimaryValue($val)
    {
        $this->setPrimaryValue = $val;
    }
    public function getPrimaryValue()
    {
        return $this->setPrimaryValue;
    }
    public function LockUnLockTable(bool $lock = true)
    {
        $table = $this->getTable();
        if ($lock)
            DB::raw('lock tables ' . $table . ' write');
        else
            DB::raw('unlock tables');
    }
}
