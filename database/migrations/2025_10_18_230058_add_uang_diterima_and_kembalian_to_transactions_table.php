<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // cek dulu apakah kolom belum ada
            if (!Schema::hasColumn('transactions', 'uang_diterima')) {
                $table->decimal('uang_diterima', 15, 2)->nullable()->after('total');
            }

            if (!Schema::hasColumn('transactions', 'kembalian')) {
                $table->decimal('kembalian', 15, 2)->nullable()->after('uang_diterima');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // cek dulu sebelum drop
            if (Schema::hasColumn('transactions', 'uang_diterima')) {
                $table->dropColumn('uang_diterima');
            }

            if (Schema::hasColumn('transactions', 'kembalian')) {
                $table->dropColumn('kembalian');
            }
        });
    }
};
