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
        Schema::create('product_sales_summary', function (Blueprint $table) {
            $table->id();
        
            // Relasi produk
            $table->integer('idpenginput');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->foreignId('variation_id')->nullable()->constrained('product_variations')->nullOnDelete();
        
            // Periode data (harian)
            $table->date('date');
        
            // Agregasi penjualan
            $table->integer('total_qty')->default(0);
            $table->decimal('total_sales', 15, 2)->default(0);
        
            // Audit
            $table->timestamps();
        
            // Unique index agar tidak dobel per hari
            $table->unique(['product_id', 'variation_id', 'date'], 'sales_summary_unique');
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
