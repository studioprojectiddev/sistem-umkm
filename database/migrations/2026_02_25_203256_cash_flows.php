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
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();

            $table->foreignId('warehouse_id')->nullable()
                ->constrained('warehouses')->onDelete('cascade');

            $table->enum('type', ['income', 'expense']);

            $table->string('category'); // Listrik, Gaji, Modal, dll
            $table->string('reference')->nullable(); // nomor invoice / referensi
            $table->text('description')->nullable();

            $table->decimal('amount', 18, 2);
            $table->date('transaction_date');

            $table->foreignId('created_by')
                ->constrained('users')->onDelete('cascade');

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
