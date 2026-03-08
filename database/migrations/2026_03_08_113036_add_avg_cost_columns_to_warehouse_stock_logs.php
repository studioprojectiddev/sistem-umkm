<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvgCostColumnsToWarehouseStockLogs extends Migration
{
    public function up()
    {
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {

            $table->decimal('avg_cost_before', 15, 2)
                ->nullable()
                ->after('price');

            $table->decimal('avg_cost_after', 15, 2)
                ->nullable()
                ->after('avg_cost_before');

        });
    }

    public function down()
    {
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {

            $table->dropColumn(['avg_cost_before', 'avg_cost_after']);

        });
    }
}