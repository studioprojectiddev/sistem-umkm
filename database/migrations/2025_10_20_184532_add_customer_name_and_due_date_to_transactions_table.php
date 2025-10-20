<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Menambahkan kolom nama pembeli (opsional jika tidak pakai relasi customer_id)
            $table->string('customer_name')->nullable()->after('customer_id');

            // Menambahkan kolom tanggal jatuh tempo (jika transaksi utang)
            $table->date('due_date')->nullable()->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['customer_name', 'due_date']);
        });
    }
};
