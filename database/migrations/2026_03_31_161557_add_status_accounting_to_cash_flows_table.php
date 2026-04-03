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
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->string('status_accounting')->default('waiting_check')->after('amount');
            $table->foreignId('checked_by')->nullable()->after('status_accounting')->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable()->after('checked_by');
            $table->foreignId('posted_by')->nullable()->after('checked_at')->constrained('users')->nullOnDelete();
            $table->timestamp('posted_at')->nullable()->after('posted_by');
            $table->foreignId('void_by')->nullable()->after('posted_at')->constrained('users')->nullOnDelete();
            $table->timestamp('void_at')->nullable()->after('void_by');
            $table->text('void_reason')->nullable()->after('void_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->dropForeign(['checked_by']);
            $table->dropForeign(['posted_by']);
            $table->dropForeign(['void_by']);
            $table->dropColumn([
                'status_accounting',
                'checked_by',
                'checked_at',
                'posted_by',
                'posted_at',
                'void_by',
                'void_at',
                'void_reason',
            ]);
        });
    }
};
