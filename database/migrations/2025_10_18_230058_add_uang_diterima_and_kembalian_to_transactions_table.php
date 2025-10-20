<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('uang_diterima', 15, 2)->nullable()->after('total');
            $table->decimal('kembalian', 15, 2)->nullable()->after('uang_diterima');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['uang_diterima', 'kembalian']);
        });
    }
};