<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_stock_transactions', function (Blueprint $table) {

            // Tambahan sesuai form
            $table->integer('min_stock')->nullable()->after('quantity');
            $table->string('rack_position')->nullable()->after('min_stock');

        });
    }

    public function down(): void
    {
        Schema::table('warehouse_stock_transactions', function (Blueprint $table) {
            $table->dropColumn(['min_stock', 'rack_position']);
        });
    }
};