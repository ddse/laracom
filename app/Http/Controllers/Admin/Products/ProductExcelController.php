<?php

namespace App\Http\Controllers\Admin\Products;

use App\Http\Controllers\Controller;
use App\Services\ExcelReader;
use App\Shop\Categories\Exceptions\CategoryNotFoundException;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\ProductMetas\ProductMeta;
use App\Shop\Products\Product;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Requests\CreateProductRequest;
use App\Shop\ShinA\Services\ExcelReaderService;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductExcelController extends ProductController
{
    public function index()
    {
        $fileName = 'F:\\shina\\manafactory\\UHA味覚糖様2020年1月新商品＊.xlsx';
        $path = (storage_path('app\\excel_upload\\UHA味覚糖様2020年1月新商品＊.xlsx'));
        Storage::disk('local')->makeDirectory('excel_upload');
        copy($fileName, $path);

        $result_r = ExcelReaderService::run($path);

        $request = new CreateProductRequest();
        $products = array();
        foreach ($result_r as $product) {
            $product = ExcelReaderService::toObject($product);
            dd($product);
            // $product = $product->getAttributes();
            $im = file_get_contents($product["image"]);
            $type = pathinfo($product["image"], PATHINFO_EXTENSION);
            $filename = pathinfo($product["image"], PATHINFO_FILENAME);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($im);
            // $product['image'] = $base64;
            $product['name'] = $product['product_value'];
            $product['sku'] = $product['code_jan_value'];
            $product['price'] =  strpos($product['price_value'], '円') > 0 ? substr($product['price_value'], 0, strpos($product['price_value'], '円')) : $product['price_value'];
            $product['brand_id'] = null;
            $product['slug'] = null;
            $product['description'] = null;
            $product['cover'] = null;
            $product['quantity'] = 0;
            $product['sale_price'] = null;
            $product['status'] = ProductRepository::STATUS_ON;
            $product['length'] = 0;
            $product['width'] = 0;
            $product['height'] = 0;
            // $product['weight'] = 0;
            $product['distance_unit'] = 0;
            $product['mass_unit'] = 0;
            $product['meta_limited_date'] = $product['limited_value'];
            $product['meta_code_tif_value']  = $product['code_tif_value'];

            $product['meta_ps'] = json_encode(['W' => $product['ps_w_V'], 'H' => $product['ps_h_V'], 'D' => $product['ps_d_V']]);
            $product['meta_bl'] = json_encode(['W' => $product['bl_w_V'], 'H' => $product['bl_h_V'], 'D' => $product['bl_d_V']]);
            $product['meta_cs'] = json_encode(['W' => $product['cs_w_V'], 'H' => $product['cs_h_V'], 'D' => $product['cs_d_V']]);

            $product['meta_quantity'] = json_encode([$product['quantity_1'], $product['quantity_2']]);
            $meta_release = [];
            $meta_feature = [];
            for ($i = 1; $i < 9; $i++) {
                $meta_release[] = [$product['release_area_' . $i] => $product['release_date_' . $i]];
                unset($product['release_date_' . $i]);
                unset($product['release_area_' . $i]);
                if ($i < 7) {
                    $meta_feature[] = $product['feature_' . $i];
                    unset($product['feature_' . $i]);
                }
            }
            $product['meta_release'] = json_encode($meta_release);
            $product['meta_feature'] = json_encode($meta_feature);

            //get catgeory
            try {
                $category = $this->categoryRepo->findCategoryByName($product['category_value']);
            } catch (ModelNotFoundException $ex) {
                $category = $this->categoryRepo->createCategory([
                    'name' => $product['category_value'],
                    'slug' => $product['category_value'],
                    'status' => CategoryRepository::STATUS_ON,
                ]);
            }

            $product['categories']  = [$category->id];

            try {
                $brand = $this->brandRepo->findBrandByName($product['meika_value']);
            } catch (ModelNotFoundException $ex) {
                $brand = $this->brandRepo->createBrand([
                    'name' => $product['meika_value']
                ]);
            }

            $product['brand_id']  = $brand->id;

            // dd([$product]);
            //Remove array key before change key
            unset($product['meika_name']);
            unset($product['category_name']);
            unset($product['product_name']);
            unset($product['code_jan_label']);
            unset($product['code_jan_value']);
            unset($product['code_tif_label']);
            unset($product['price_label']);
            unset($product['price_value']);
            unset($product['product_value']);
            unset($product['product_value']);
            unset($product['release_area_label']);
            unset($product['release_date_label']);
            unset($product['feature_label']);
            unset($product['bl_label']);
            unset($product['cs_label']);
            unset($product['ps_label']);

            unset($product['ps_w_V']);
            unset($product['ps_h_V']);
            unset($product['ps_d_V']);
            unset($product['bl_w_V']);
            unset($product['bl_h_V']);
            unset($product['bl_d_V']);
            unset($product['cs_w_V']);
            unset($product['cs_h_V']);
            unset($product['cs_d_V']);

            unset($product['ps_w_N']);
            unset($product['ps_h_N']);
            unset($product['ps_d_N']);
            unset($product['bl_w_N']);
            unset($product['bl_h_N']);
            unset($product['bl_d_N']);
            unset($product['cs_w_N']);
            unset($product['cs_h_N']);
            unset($product['cs_d_N']);
            unset($product['limited_label']);
            unset($product['limited_value']);
            unset($product['code_tif_value']);
            unset($product['standard']);

            unset($product['quantity_1']);
            unset($product['quantity_x']);
            unset($product['quantity_2']);
            unset($product['barcode']);



            $request->replace($product);



            $data = $request->except('_token', '_method', 'image');
            $data['slug'] = str_slug($request->input('name'));
            $metas = array_filter($request->input(), function ($key) {
                return strpos($key, 'meta_') === 0;
            }, ARRAY_FILTER_USE_KEY);


            $file = new UploadedFile($product["image"], $filename);
            if ($file instanceof UploadedFile) {
                $data['cover'] = $this->productRepo->saveCoverImage($file);
            }

            $product = $this->productRepo->createProduct($data);

            $productRepo = new ProductRepository($product);

            if ($request->hasFile('image')) {
                $productRepo->saveProductImages(collect($request->file('image')));
            }

            if ($request->has('categories')) {
                $productRepo->syncCategories($request->input('categories'));
            } else {
                $productRepo->detachCategories();
            }

            if (!is_null($metas)) {
                $productRepo->syncMetas($metas, $product->id);
            } else {
                $productRepo->detachMetas();
            }
            // dd($product);

            // $products[] = $product;
        }
        // $request->replace($products);
        // dd($request->all());
        foreach ($result_r as $column) {
            echo ExcelReaderService::htmlExample($column);
        }
        return "OK";
    }
}
