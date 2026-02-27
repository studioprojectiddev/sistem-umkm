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
        Schema::create('cashflow_closings', function (Blueprint $table) {
            $table->id();
            $table->integer('month');
            $table->integer('year');
            $table->timestamp('closed_at');
            $table->foreignId('closed_by')->constrained('users');
            $table->timestamps();

            $table->unique(['month','year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashflow_closings');
    }
};
