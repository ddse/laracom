<?php

namespace App\Shop\Products\Services;

use App\Shop\Base\Services\BaseServiceInterface;
use App\Shop\Products\Requests\CreateProductRequest;
use App\Shop\Products\Requests\UpdateProductRequest;

interface ProductServiceInterface extends BaseServiceInterface
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateProductRequest $request);
    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Shop\Products\Exceptions\ErrorException
     */
    public function update(UpdateProductRequest $request, int $id);
}
