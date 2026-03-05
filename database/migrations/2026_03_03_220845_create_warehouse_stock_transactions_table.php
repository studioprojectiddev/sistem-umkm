<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouse_stock_transactions', function (Blueprint $table) {
            $table->id();

            // Relasi
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->onDelete('cascade');

            // Jenis aksi
            $table->enum('action_type', ['add', 'reduce']);

            // Stok
            $table->integer('quantity');

            // Keuangan
            $table->decimal('price', 15, 2)->nullable();
            $table->decimal('total', 15, 2)->nullable();
            $table->decimal('paid', 15, 2)->default(0);
            $table->decimal('remaining', 15, 2)->default(0);

            // Supplier
            $table->string('supplier_name')->nullable();
            $table->date('due_date')->nullable();

            // User
            $table->unsignedBigInteger('idpenginput');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_stock_transactions');
    }
};