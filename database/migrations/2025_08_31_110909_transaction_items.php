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
        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
        
            // Relasi ke transaksi
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
        
            // Relasi ke produk
            $table->integer('idpenginput');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete(); // jika produk punya variasi
        
            // Detail item
            $table->integer('quantity')->default(1);
            $table->decimal('price', 15, 2); // harga per item (jual/beli)
            $table->decimal('discount', 15, 2)->default(0); // diskon per item
            $table->decimal('subtotal', 15, 2); // quantity * price - discount
        
            // Catatan item
            $table->string('unit', 50)->nullable(); // pcs, kg, liter
            $table->text('notes')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['transaction_id']);
            $table->dropForeign(['product_id']);
            $table->dropForeign(['variation_id']);
        });

        Schema::dropIfExists('transaction_items');
    }

};
