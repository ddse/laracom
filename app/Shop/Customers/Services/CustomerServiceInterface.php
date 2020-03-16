<?php

namespace App\Shop\Customers\Services;

use App\Shop\Base\Services\BaseServiceInterface;
use App\Shop\Customers\Requests\CreateCustomerRequest;
use App\Shop\Customers\Requests\UpdateCustomerRequest;

interface CustomerServiceInterface extends BaseServiceInterface
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index();

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create();

    /**
     * Store a newly created resource in storage.
     *
     * @param  CreateCustomerRequest $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCustomerRequest $request);

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function show(int $id);

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id);

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateCustomerRequest $request
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \App\Shop\Customers\Exceptions\CustomerUpdateErrorException
     */
    public function update(UpdateCustomerRequest $request, int $id);

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy($id);
}
