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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
        
            // Identitas transaksi
            $table->string('invoice_number')->unique(); // Nomor invoice
            $table->enum('transaction_type', ['sale', 'purchase', 'return_sale', 'return_purchase'])
                  ->default('sale'); // Jenis transaksi
        
            // Relasi ke UMKM owner / kasir
            $table->integer('idpenginput');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // kasir/owner yg input
            $table->foreignId('customer_id')->nullable()->constrained('users')->nullOnDelete(); // opsional: relasi ke pelanggan
            $table->foreignId('supplier_id')->nullable()->constrained('users')->nullOnDelete(); // opsional: relasi ke supplier (jika pembelian)
        
            // Waktu transaksi
            $table->dateTime('transaction_date')->default(DB::raw('CURRENT_TIMESTAMP'));
        
            // Detail pembayaran
            $table->decimal('subtotal', 15, 2)->default(0); 
            $table->decimal('discount', 15, 2)->default(0); // diskon total
            $table->decimal('tax', 15, 2)->default(0);      // pajak
            $table->decimal('shipping_cost', 15, 2)->default(0); // ongkir
            $table->decimal('total', 15, 2)->default(0);    // total akhir
        
            $table->enum('payment_status', ['unpaid', 'partial', 'paid', 'refunded'])
                  ->default('unpaid');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'ewallet', 'credit_card'])
                  ->nullable();
        
            // Status transaksi
            $table->enum('status', ['pending', 'completed', 'cancelled', 'refunded'])
                  ->default('pending');
        
            // Catatan tambahan
            $table->text('notes')->nullable();
            $table->string('customer_name')->nullable()->after('customer_id'); // nama pembeli / pelanggan
            $table->date('due_date')->nullable()->after('payment_status');
            $table->decimal('uang_diterima', 15, 2)->nullable()->after('total');
            $table->decimal('kembalian', 15, 2)->nullable()->after('uang_diterima');
        
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        
            $table->timestamps();
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
