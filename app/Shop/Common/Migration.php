<?php namespace App\Shop\Common;

use App\Shop\Common\Traits\CommonColumns;
use Illuminate\Database\Migrations\Migration as MigrationsMigration;

abstract class Migration extends MigrationsMigration{
    use CommonColumns;
}