<?php

namespace App\Shop\Cities\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;
use App\Shop\Cities\City;

interface CityRepositoryInterface extends BaseRepositoryInterface
{
    public function listCities();

    public function findCityById(int $id) : City;

    public function updateCity(array $params) : bool;

    public function findCityByName(string $name) : City;
}
