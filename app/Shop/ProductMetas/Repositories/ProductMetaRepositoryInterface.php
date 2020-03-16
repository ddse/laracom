<?php

namespace App\Shop\ProductMetas\Repositories;

use App\Shop\ProductMetas\ProductMeta;
use Illuminate\Support\Collection;
use App\Shop\Base\Repositories\BaseRepositoryInterface;

interface ProductMetaRepositoryInterface extends BaseRepositoryInterface
{
    public function createProductMeta(array $params) : ProductMeta;

    public function updateProductMeta(array $params) : ProductMeta;

    public function findProductMetaById(int $id) : ProductMeta;
    
    public function deleteProductMeta() : bool;
}
