<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambah kolom customer_name jika belum ada
            if (!Schema::hasColumn('transactions', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }

            // Tambah kolom due_date jika belum ada
            if (!Schema::hasColumn('transactions', 'due_date')) {
                $table->date('due_date')->nullable()->after('payment_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('transactions', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};
