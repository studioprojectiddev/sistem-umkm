<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            // Tambahkan kolom action_type setelah kolom stock (ubah posisi sesuai kebutuhan)
            $table->enum('action_type', ['add', 'reduce'])
                  ->nullable()
                  ->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {
            $table->dropColumn('action_type');
        });
    }
};