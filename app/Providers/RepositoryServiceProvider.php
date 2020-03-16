<?php

namespace App\Providers;

use App\Shop\Addresses\Repositories\AddressRepository;
use App\Shop\Attributes\Repositories\AttributeRepository;
use App\Shop\AttributeValues\Repositories\AttributeValueRepository;
use App\Shop\Brands\Repositories\BrandRepository;
use App\Shop\Carts\Repositories\CartRepository;
use App\Shop\Categories\Repositories\CategoryRepository;
use App\Shop\Cities\Repositories\CityRepository;
use App\Shop\Countries\Repositories\CountryRepository;
use App\Shop\Couriers\Repositories\CourierRepository;
use App\Shop\Customers\Repositories\CustomerRepository;
use App\Shop\Customers\Services\CustomerService;
use App\Shop\Employees\Repositories\EmployeeRepository;
use App\Shop\Orders\Repositories\OrderRepository;
use App\Shop\OrderStatuses\Repositories\OrderStatusRepository;
use App\Shop\Permissions\Repositories\PermissionRepository;
use App\Shop\ProductAttributes\Repositories\ProductAttributeRepository;
use App\Shop\ProductMetas\Repositories\ProductMetaRepository;
use App\Shop\Products\Repositories\ProductRepository;
use App\Shop\Products\Services\ProductService;
use App\Shop\Provinces\Repositories\ProvinceRepository;
use App\Shop\Roles\Repositories\RoleRepository;
use App\Shop\Shipping\ShippingInterface;
use App\Shop\Shipping\Shippo\ShippoShipmentRepository;
use App\Shop\States\Repositories\StateRepository;
use Illuminate\Foundation\ProviderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    private $repositories;
    private $services;
    function __construct($app)
    {
        parent::__construct($app);
        // $this->repositories = [
        //     CategoryRepository::class => [1, 0, 'Categories'],
        //     'State' => [1, 0],
        //     'Brand' => [1, 0],
        //     'ProductAttribute' => [1, 0],
        //     'AttributeValue' => [1, 0],
        //     'Attribute' => [1, 0],
        //     'Employee' => [1, 0],
        //     'Customer' => [1, 1],
        //     'Product' => [1, 1],
        //     'Address' => [1, 0, 'Addresses'],
        //     'Country' => [1, 0, 'Countries'],
        //     'Province' => [1, 0],
        //     'City' => [1, 0],
        //     'Order' => [1, 0, 'Orders'],
        //     'OrderStatus' => [1, 0],
        //     'Courier' => [1, 0],
        //     'Cart' => [1, 0],
        //     'Role' => [1, 0],
        //     'Permission' => [1, 0],
        //     'ProductMeta' => [1, 0],
        // ];
        $this->repositories = [
            CategoryRepository::class,
            StateRepository::class,
            BrandRepository::class,
            ProductAttributeRepository::class,
            AttributeRepository::class,
            EmployeeRepository::class,
            CustomerRepository::class,
            ProductRepository::class,
            AddressRepository::class,
            CountryRepository::class,
            ProviderRepository::class,
            CityRepository::class,
            OrderRepository::class,
            CourierRepository::class,
            CartRepository::class,
            RoleRepository::class,
            PermissionRepository::class,
            ProductMetaRepository::class,
            AttributeValueRepository::class,
            ProvinceRepository::class,
            OrderStatusRepository::class
        ];
        $this->services = [
            ProductService::class,
            CustomerService::class,
        ];
    }
    public function register()
    {
        foreach ($this->repositories as $repository) {
            $repository_imple = class_implements($repository);
            $this->app->bind(
                end($repository_imple),
                $repository
            );
        }
        foreach ($this->services as $service) {
            $service_imple = class_implements($service);
            $this->app->bind(
                end($service_imple),
                $service
            );
        }
        $this->app->bind(
            ShippingInterface::class,
            ShippoShipmentRepository::class
        );
    }
}
