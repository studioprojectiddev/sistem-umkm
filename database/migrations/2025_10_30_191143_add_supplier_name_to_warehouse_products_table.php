<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            // Tambahkan kolom supplier_name (boleh null)
            $table->string('supplier_name')->nullable()->after('rack_position');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->dropColumn('supplier_name');
        });
    }
};
