<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvgCostToWarehouseProductsTable extends Migration
{
    public function up()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {

            $table->decimal('avg_cost', 15, 2)
                ->default(0)
                ->after('stock');

        });
    }

    public function down()
    {
        Schema::table('warehouse_products', function (Blueprint $table) {

            $table->dropColumn('avg_cost');

        });
    }
}