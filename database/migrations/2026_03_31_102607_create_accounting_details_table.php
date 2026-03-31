<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounting_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('accounting_id')->constrained('accountings')->cascadeOnDelete();
            $table->foreignId('coa_id')->constrained('chart_of_accounts');
            $table->decimal('debit', 16, 2)->default(0);
            $table->decimal('credit', 16, 2)->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->index('accounting_id');
            $table->index('coa_id');
        });

        DB::statement('ALTER TABLE accounting_details ADD CONSTRAINT accounting_details_debit_credit_xor_check CHECK ((debit > 0 AND credit = 0) OR (debit = 0 AND credit > 0))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_details');
    }
};
