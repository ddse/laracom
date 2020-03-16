<?php

namespace App\Shop\Base;

use App\Shop\Carts\Contracts\Buyable;
use Illuminate\Database\Eloquent\Model;

abstract class BaseModel extends Model implements Buyable
{
    public function getAttributes()
    {
        return $this->attributes;
    }
}
