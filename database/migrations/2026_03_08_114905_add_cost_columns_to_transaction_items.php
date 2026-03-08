<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCostColumnsToTransactionItems extends Migration
{
    public function up()
    {
        Schema::table('transaction_items', function (Blueprint $table) {

            $table->decimal('cost_price',15,2)->default(0)->after('price');

            $table->decimal('total_cost',15,2)->default(0)->after('cost_price');

        });
    }

    public function down()
    {
        Schema::table('transaction_items', function (Blueprint $table) {

            $table->dropColumn(['cost_price','total_cost']);

        });
    }
}