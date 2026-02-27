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
        Schema::create('account_openings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->constrained('accounts')
                ->onDelete('cascade');

            $table->integer('month');
            $table->integer('year');

            $table->decimal('opening_balance',18,2);

            $table->timestamps();

            $table->unique(['account_id','month','year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_openings');
    }
};
