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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Identitas produk
            $table->string('name'); // Nama produk
            $table->string('slug')->unique(); // URL friendly
            $table->string('sku')->unique()->nullable(); // Stock Keeping Unit
            $table->string('barcode')->nullable(); // Barcode opsional
            $table->text('description')->nullable(); // Deskripsi produk

            // Relasi UMKM owner (user_id wajib)
            $table->integer('idpenginput');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Relasi kategori (opsional, karena punya tabel sendiri)
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();

            // Harga & stok
            $table->decimal('price', 12, 2); // harga jual
            $table->decimal('discount_price', 12, 2)->nullable(); // harga diskon
            $table->decimal('cost_price', 12, 2)->nullable(); // harga modal
            $table->string('unit', 50)->default('pcs'); // satuan (pcs, kg, liter, dll)

            // Jenis produk
            $table->enum('product_type', ['goods', 'service'])->default('goods');

            // Batch / Expired (opsional, F&B atau farmasi)
            $table->date('expiry_date')->nullable();
            $table->string('batch_number')->nullable();

            // Media / Gambar
            $table->string('thumbnail')->nullable(); // gambar utama
            $table->json('images')->nullable(); // galeri produk

            // Variasi / atribut custom (opsional, JSON)
            $table->json('attributes')->nullable(); // contoh: {"warna":"Merah","ukuran":"L"}

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);

            // Promosi
            $table->boolean('is_promo')->default(false);
            $table->decimal('promo_price', 12, 2)->nullable();
            $table->date('promo_start')->nullable();
            $table->date('promo_end')->nullable();

            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();

            // AI Insight (opsional untuk analisis / rekomendasi)
            $table->json('ai_insights')->nullable();

            $table->timestamps();
        });
        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
