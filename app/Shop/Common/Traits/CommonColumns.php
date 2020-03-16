<?php

namespace App\Shop\Common\Traits;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

trait CommonColumns
{

    public function runColmuns(Blueprint $table)
    {
        $table->integer('created_by')->default(0);
        $table->integer('updated_by')->default(0);
        $table->softDeletes()->nullable();
        $table->timestamp('created_at')->nullable();
        //->default(DB::raw('CURRENT_TIMESTAMP'));

        $table->timestamp('updated_at')->nullable();
        //->default(DB::raw('CURRENT_TIMESTAMP'));
        
    }
}
