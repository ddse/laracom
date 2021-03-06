<?php

namespace App\Shop\Base\Services;

use App\Shop\Base\Repositories\BaseRepositoryInterface;

abstract class BaseService implements BaseServiceInterface
{
    /**
     * @var BaseRepositoryInterface
     */
    protected $repository;
    /**
     * CustomerService constructor.
     * 
     * @param Customer $customer
     */
    public function __construct(BaseRepositoryInterface $repository)
    {
        //parent::__construct($customer);
        $this->repository = $repository;
    }
    
    // /**
    //  * Display a listing of the resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function index()
    // {
    //     $list = $this->repository->listCustomers('created_at', 'desc');

    //     if (request()->has('q')) {
    //         $list = $this->customerRepo->searchCustomer(request()->input('q'));
    //     }

    //     $customers = $list->map(function (Customer $customer) {
    //         return $this->transformCustomer($customer);
    //     })->all();


    //     return $this->customerRepo->paginateArrayResults($customers);
    // }

    // /**
    //  * Show the form for creating a new resource.
    //  *
    //  * @return \Illuminate\Http\Response
    //  */
    // public function create()
    // {
    //     return [];
    // }

    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  CreateCustomerRequest $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(CreateCustomerRequest $request)
    // {
        
    //     return $this->customerRepo->createCustomer($request->except('_token', '_method'));

    // }

    // /**
    //  * Display the specified resource.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show(int $id)
    // {
    //     $customer = $this->customerRepo->findCustomerById($id);
        
    //     return [
    //         'customer' => $customer,
    //         'addresses' => $customer->addresses
    //     ];
    // }

    // /**
    //  * Show the form for editing the specified resource.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit($id)
    // {
    //     return $this->customerRepo->findCustomerById($id);
    // }

    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  UpdateCustomerRequest $request
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(UpdateCustomerRequest $request, $id)
    // {
    //     $customer = $this->customerRepo->findCustomerById($id);

    //     $update = new CustomerRepository($customer);
    //     $data = $request->except('_method', '_token', 'password');

    //     if ($request->has('password')) {
    //         $data['password'] = bcrypt($request->input('password'));
    //     }

    //     $update->updateCustomer($data);

    //     $request->session()->flash('message', 'Update successful');
        
    //     return $id;
    // }

    // /**
    //  * Remove the specified resource from storage.
    //  *
    //  * @param  int $id
    //  *
    //  * @return \Illuminate\Http\Response
    //  * @throws \Exception
    //  */
    // public function delete($id)
    // {
    //     $customer = $this->customerRepo->findCustomerById($id);

    //     $customerRepo = new CustomerRepository($customer);
    //     $customerRepo->deleteCustomer();
    // }

    // public function destroy($id)
    // {
    //     $customer = $this->customerRepo->findCustomerById($id);

    //     $customerRepo = new CustomerRepository($customer);
    //     $customerRepo->deleteCustomer();
    // }
}