<?php

namespace App\Shop\ProductMetas;

use App\Shop\Products\Product;
use Illuminate\Database\Eloquent\Model;

class ProductMeta extends Model
{
    //
    protected $fillable = ['name'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function products()
    {
        return $this->hasOne(Product::class);
    }
}
