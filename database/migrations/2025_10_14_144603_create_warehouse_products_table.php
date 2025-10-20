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
        Schema::create('warehouse_products', function (Blueprint $table) {
            $table->id();

            // 🔗 Relasi utama
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');

            // 📦 Stok detail
            $table->integer('stock')->default(0); // stok aktual di gudang
            $table->integer('reserved')->default(0); // stok yang sedang dipesan / belum dikurangi
            $table->integer('min_stock')->default(0); // ambang batas notifikasi stok minimum

            // 📍 Lokasi fisik di gudang
            $table->string('rack_position')->nullable(); // contoh: A1-03 atau Rak B-Baris 2

            // ⚙️ Status tambahan
            $table->boolean('is_active')->default(true); // jika produk sementara tidak tersedia di gudang

            // 🕓 Timestamps
            $table->timestamps();

            // 🧩 Unique constraint: satu produk/variasi hanya bisa 1x per gudang
            $table->unique(['warehouse_id', 'product_id', 'variation_id'], 'unique_warehouse_product');

            // 📊 Index tambahan (untuk performa query cepat)
            $table->index(['warehouse_id']);
            $table->index(['product_id']);
            $table->index(['variation_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_products');
    }
};
