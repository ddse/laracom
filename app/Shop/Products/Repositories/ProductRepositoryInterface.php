<?php

namespace App\Shop\Products\Repositories;

use App\Shop\AttributeValues\AttributeValue;
use App\Shop\Base\Repositories\BaseRepositoryInterface;
use App\Shop\Brands\Brand;
use App\Shop\ProductAttributes\ProductAttribute;
use App\Shop\ProductMetas\Repositories\ProductMetaRepositoryInterface;
use App\Shop\Products\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    public function listProducts(string $order = 'id', string $sort = 'desc', array $columns = ['*']): Collection;

    public function createProduct(array $data): Product;

    public function updateProduct(array $data): bool;

    public function findProductById(int $id): Product;

    public function deleteProduct(Product $product): bool;

    public function removeProduct(): bool;

    public function detachCategories();

    public function getCategories(): Collection;

    public function syncCategories(array $params);

    public function deleteFile(array $file, $disk = null): bool;

    public function deleteThumb(string $src): bool;

    public function findProductBySlug(array $slug): Product;

    public function searchProduct(string $text): Collection;

    public function findProductImages(): Collection;

    public function saveCoverImage(UploadedFile $file): string;

    public function saveProductImages(Collection $collection);

    public function saveProductAttributes(ProductAttribute $productAttribute): ProductAttribute;

    public function listProductAttributes(): Collection;

    public function removeProductAttribute(ProductAttribute $productAttribute): ?bool;

    public function saveCombination(ProductAttribute $productAttribute, AttributeValue ...$attributeValues): Collection;

    public function listCombinations(): Collection;

    public function findProductCombination(ProductAttribute $attribute);

    public function saveBrand(Brand $brand);

    public function findBrand();

    /**
     * Detach the Metas
     */
    public function detachMetas();

    /**
     * Return the Metas which the product is associated with
     *
     * @return Collection
     */
    public function getMetas(): Collection;

    /**
     * Sync the Metas
     *
     * @param array $params
     */
    public function syncMetas(array $params);
}
