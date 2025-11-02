<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stock_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');
            
            // Jenis aksi (add, reduce, transfer_in, transfer_out)
            $table->enum('action_type', ['add', 'reduce', 'transfer_in', 'transfer_out']);
            
            // Jumlah stok yang ditambahkan atau dikurangi
            $table->integer('quantity')->default(0);
            
            // Catatan atau deskripsi tambahan (opsional)
            $table->string('note')->nullable();

            // Untuk melacak siapa yang melakukan perubahan
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_logs');
    }
};