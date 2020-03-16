<?php

namespace App\Shop\Countries\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;
use App\Shop\Countries\Country;
use Illuminate\Support\Collection;

interface CountryRepositoryInterface extends BaseRepositoryInterface
{
    public function updateCountry(array $params) : Country;

    public function listCountries(string $order = 'id', string $sort = 'desc') : Collection;

    public function createCountry(array $params) : Country;

    public function findCountryById(int $id) : Country;

    public function findProvinces();

    public function listStates() : Collection;
}
