<?php

namespace App\Shop\ProductAttributes\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;

interface ProductAttributeRepositoryInterface extends BaseRepositoryInterface
{
    public function findProductAttributeById(int $id);
}
