<?php

namespace App\Shop\Couriers\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;
use App\Shop\Couriers\Courier;
use Illuminate\Support\Collection;

interface CourierRepositoryInterface extends BaseRepositoryInterface
{
    public function createCourier(array $data) : Courier;

    public function updateCourier(array $params) : bool;

    public function findCourierById(int $id) : Courier;

    public function listCouriers(string $order = 'id', string $sort = 'desc') : Collection;

    public function deleteCourier();
}
