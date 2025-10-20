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
        // 🔹 Hapus kolom stock dari products
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'stock')) {
                $table->dropColumn('stock');
            }
            if (Schema::hasColumn('products', 'min_stock')) {
                $table->dropColumn('min_stock');
            }
        });

        // 🔹 Hapus kolom stock dari product_variations
        Schema::table('product_variations', function (Blueprint $table) {
            if (Schema::hasColumn('product_variations', 'stock')) {
                $table->dropColumn('stock');
            }
        });

        // 🔹 Tambahkan kolom baru di warehouse_products
        Schema::table('warehouse_products', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouse_products', 'reserved')) {
                $table->integer('reserved')->default(0)->after('stock');
            }
            if (!Schema::hasColumn('warehouse_products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('rack_position');
            }
        });
    }

    public function down(): void
    {
        // Rollback perubahan
        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0);
            $table->integer('min_stock')->default(0);
        });

        Schema::table('product_variations', function (Blueprint $table) {
            $table->integer('stock')->default(0);
        });

        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->dropColumn(['reserved', 'is_active']);
        });
    }
};
