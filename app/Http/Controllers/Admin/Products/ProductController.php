<?php

namespace App\Http\Controllers\Admin\Products;

use App\Shop\Products\Requests\CreateProductRequest;
use App\Shop\Products\Requests\UpdateProductRequest;
use App\Shop\Base\Controller\BaseController;
use App\Shop\Products\Services\ProductServiceInterface;
use Illuminate\Http\Request;

class ProductController extends BaseController
{
    function __construct(ProductServiceInterface $productService)
    {
        parent::__construct($productService, 'admin.products', 'admin.products');
    }
    /**
     * @inheritdoc
     */
    public function setMiddleware(): array
    {
        return [
            'guard' => 'employee',
            'permission' => 'product'
        ];
    }
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index()
    // {
    //     return view('admin.products.list', [
    //         'products' => $this->productService->index()
    //     ]);
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {

    //     return view('admin.products.create', $this->productService->create());
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  CreateProductRequest $request
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(CreateProductRequest $request)
    // {
    //     return redirect()
    //         ->route('admin.products.edit', $this->productService->store($request)->product->id)
    //         ->with('message', 'Create successful');
    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int $id
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(int $id)
    // {
    //     $product = $this->productService->show($id);
    //     return view('admin.products.show', compact('product'));
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  int $id
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit(int $id)
    // {
    //     return view('admin.products.edit', $this->productService->edit($id));
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  UpdateProductRequest $request
    //  * @param  int $id
    //  *
    //  * @return \Illuminate\Http\Response
    //  * @throws \App\Shop\Products\Exceptions\ProductUpdateErrorException
    //  */
    // public function update(UpdateProductRequest $request, int $id)
    // {
    //     $id = $this->productService->update($request, $id);
    //     return redirect()->route('admin.products.edit', $id)
    //         ->with('message', 'Update successful');
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int $id
    //  *
    //  * @return \Illuminate\Http\Response
    //  * @throws \Exception
    //  */
    // public function destroy($id)
    // {
    //     $id = $this->productService->destroy($id);
    //     return redirect()->route('admin.products.index')->with('message', 'Delete successful');
    // }

    // /**
    //  * @param Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function removeImage(Request $request)
    // {
    //     $this->productRepo->deleteFile($request->only('product', 'image'), 'uploads');
    //     return redirect()->back()->with('message', 'Image delete successful');
    // }

    // /**
    //  * @param Request $request
    //  *
    //  * @return \Illuminate\Http\RedirectResponse
    //  */
    // public function removeThumbnail(Request $request)
    // {
    //     $this->productRepo->deleteThumb($request->input('src'));
    //     return redirect()->back()->with('message', 'Image delete successful');
    // }
}
