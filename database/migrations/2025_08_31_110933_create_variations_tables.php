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
        // 1. Master Atribut Variasi
        Schema::create('variation_attributes', function (Blueprint $table) {
            $table->id();
            $table->integer('idpenginput');
            $table->string('name'); // contoh: Warna, Ukuran, Rasa
            $table->timestamps();
        });

        // 2. Opsi untuk tiap atribut
        Schema::create('variation_options', function (Blueprint $table) {
            $table->id();
            $table->integer('idpenginput');
            $table->foreignId('attribute_id')->constrained('variation_attributes')->onDelete('cascade');
            $table->string('value'); // contoh: Merah, Biru, S, M, L
            $table->timestamps();
        });

        // 3. Variasi Produk
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->integer('idpenginput');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('name'); // contoh: Kaos Merah - M
            $table->string('sku')->nullable()->unique();
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('image')->nullable(); // gambar khusus variasi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Relasi antara variasi ↔ opsi
        Schema::create('product_variation_options', function (Blueprint $table) {
            $table->id();
            $table->integer('idpenginput');
            $table->foreignId('variation_id')->constrained('product_variations')->onDelete('cascade');
            $table->foreignId('option_id')->constrained('variation_options')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variation_options');
        Schema::dropIfExists('product_variations');
        Schema::dropIfExists('variation_options');
        Schema::dropIfExists('variation_attributes');
    }

};
