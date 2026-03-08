<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_stock_transactions', function (Blueprint $table) {

            $table->string('transaction_code')->nullable()->unique()->after('id');

            $table->foreignId('account_id')
                  ->nullable()
                  ->constrained('accounts')
                  ->nullOnDelete();

            $table->date('transaction_date')->nullable();

            $table->enum('payment_status', ['paid','partial','unpaid'])
                  ->default('unpaid');
        });
    }

    public function down(): void
    {
        Schema::table('warehouse_stock_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'transaction_code',
                'account_id',
                'transaction_date',
                'payment_status'
            ]);
        });
    }
};