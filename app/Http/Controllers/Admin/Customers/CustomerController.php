<?php

namespace App\Http\Controllers\Admin\Customers;

use App\Shop\Customers\Customer;
use App\Shop\Customers\Requests\CreateCustomerRequest;
use App\Shop\Customers\Requests\UpdateCustomerRequest;
use App\Shop\Customers\Transformations\CustomerTransformable;
use App\Shop\Base\Controller\BaseController;
use App\Shop\Customers\Repositories\CustomerRepositoryInterface;
use App\Shop\Customers\Services\CustomerServiceInterface;

class CustomerController extends BaseController
{
    /**
     * CustomerController constructor.
     * @param CustomerRepositoryInterface $customerRepository
     */

    function __construct(CustomerServiceInterface $customerRepository)
    {
        parent::__construct($customerRepository, false);
    }
    /**
     * @inheritdoc
     */
    public function setMiddleware(): array
    {
        return [
            'guard' => 'employee',
            'permission' => 'customer'
        ];
    }
}
