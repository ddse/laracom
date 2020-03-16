<?php // GetNextSequenceValue.php

namespace App\Shop\Common;

use Illuminate\Support\Facades\DB;

trait GetNextSequenceValue
{
    public static function getNextSequenceValue()
    {
        $self = new static();

        if (!$self->getIncrementing()) {
            throw new \Exception(sprintf('Model (%s) is not auto-incremented', static::class));
        }

        $sequenceName = "{$self->getTable()}_id_seq";

        // return DB::selectOne("SELECT nextval('{$sequenceName}') AS val")->val;
        return (DB::selectOne("SELECT count(*) AS val from " . $self->getTable())->val) + 1;
    }
}
