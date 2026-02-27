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
        Schema::create('account_transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('from_account_id')
                ->constrained('accounts')
                ->onDelete('cascade');

            $table->foreignId('to_account_id')
                ->constrained('accounts')
                ->onDelete('cascade');

            $table->decimal('amount',18,2);

            $table->date('transfer_date');

            $table->text('description')->nullable();

            $table->foreignId('created_by')->nullable()
                ->constrained('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_transfers');
    }
};
