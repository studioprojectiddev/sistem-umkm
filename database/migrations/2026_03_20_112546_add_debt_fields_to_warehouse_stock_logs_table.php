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
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {

            // Supplier
            if (!Schema::hasColumn('warehouse_stock_logs', 'supplier_name')) {
                $table->string('supplier_name')->nullable()->after('variation_id');
            }

            // Reference (untuk relasi ke transaksi lain)
            if (!Schema::hasColumn('warehouse_stock_logs', 'reference_type')) {
                $table->string('reference_type')->nullable()->after('transaction_code');
            }

            if (!Schema::hasColumn('warehouse_stock_logs', 'reference_id')) {
                $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            }

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('warehouse_stock_logs', function (Blueprint $table) {

            if (Schema::hasColumn('warehouse_stock_logs', 'supplier_name')) {
                $table->dropColumn('supplier_name');
            }

            if (Schema::hasColumn('warehouse_stock_logs', 'reference_type')) {
                $table->dropColumn('reference_type');
            }

            if (Schema::hasColumn('warehouse_stock_logs', 'reference_id')) {
                $table->dropColumn('reference_id');
            }

        });
    }
};
