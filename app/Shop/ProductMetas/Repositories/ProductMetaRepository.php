<?php

namespace App\Shop\ProductMetas\Repositories;

use App\Shop\Base\Repositories\BaseRepository;
use App\Shop\ProductMetas\ProductMeta;
use Illuminate\Support\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Shop\ProductMetas\Repositories\ProductMetaRepositoryInterface;
use InvalidArgumentException;

class ProductMetaRepository extends BaseRepository implements ProductMetaRepositoryInterface
{
	/**
     * ProductMetaRepository constructor.
     * 
     * @param ProductMeta $productmeta
     */
    public function __construct(ProductMeta $productmeta)
    {
        parent::__construct($productmeta);
        $this->model = $productmeta;
    }

    /**
     * Create ProductMeta
     *
     * @param array $params
     *
     * @return ProductMeta
     * @throws InvalidArgumentException
     */
    public function createProductMeta(array $params) : ProductMeta
    {
        try {
        	return ProductMeta::create($params);
        } catch (QueryException $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
    }

    /**
     * Update the productmeta
     *
     * @param array $params
     * @return ProductMeta
     */
    public function updateProductMeta(array $params) : ProductMeta
    {
        $productmeta = $this->findProductMetaById($this->model->id);
        $productmeta->update($params);
        return $productmeta;
    }

    /**
     * @param int $id
     * 
     * @return ProductMeta
     * @throws ModelNotFoundException
     */
    public function findProductMetaById(int $id) : ProductMeta
    {
        try {
            return $this->findOneOrFail($id);
        } catch (ModelNotFoundException $e) {
            throw new ModelNotFoundException($e->getMessage());
        }
    }

    /**
     * Delete a productmeta
     *
     * @return bool
     */
    public function deleteProductMeta() : bool
    {
        return $this->model->delete();
    }
}