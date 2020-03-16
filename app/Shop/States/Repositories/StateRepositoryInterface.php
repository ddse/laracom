<?php

namespace App\Shop\States\Repositories;

use App\Shop\Base\Repositories\BaseRepositoryInterface;
use Illuminate\Support\Collection;

interface StateRepositoryInterface extends BaseRepositoryInterface
{
    public function listCities() : Collection;
}