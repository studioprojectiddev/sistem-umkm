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
        Schema::create('accountings', function (Blueprint $table) {
            $table->id();
            $table->string('journal_number')->unique();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id');
            $table->decimal('total_debit', 16, 2)->default(0);
            $table->decimal('total_credit', 16, 2)->default(0);
            $table->string('status_accounting')->default('posting');
            $table->boolean('is_reversal')->default(false);
            $table->foreignId('reversal_of')->nullable()->constrained('accountings')->nullOnDelete();
            $table->unsignedBigInteger('created_by');
            $table->timestamp('created_date')->useCurrent();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->index(['reference_type', 'reference_id']);
            $table->index('created_by');

            // Optional audit fields: use created/updated date explicit in domain.
        });

        DB::statement('ALTER TABLE accountings ADD CONSTRAINT accountings_total_equal_check CHECK (total_debit = total_credit)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accountings');
    }
};
