<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {

            $table->dropForeign(['account_id']);
            $table->dropForeign(['category_id']);

            $table->dropColumn([
                'transaction_code',
                'account_id',
                'transaction_date',
                'price',
                'total',
                'paid',
                'remaining',
                'payment_status',
                'category_id'
            ]);

        });
    }

    public function down(): void
    {
        Schema::table('warehouse_products', function (Blueprint $table) {

            $table->string('transaction_code')->nullable();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->date('transaction_date')->nullable();

            $table->decimal('price',15,2)->nullable();
            $table->decimal('total',15,2)->nullable();
            $table->decimal('paid',15,2)->default(0);
            $table->decimal('remaining',15,2)->default(0);

            $table->enum('payment_status',['paid','partial','unpaid'])
                ->default('unpaid');

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('cashflow_categories')
                ->nullOnDelete();
        });
    }
};