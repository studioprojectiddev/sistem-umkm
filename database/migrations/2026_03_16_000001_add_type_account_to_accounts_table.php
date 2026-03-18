<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('accounts', 'type_account')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->enum('type_account', ['asset', 'liability', 'equity', 'revenue', 'expense', 'cogs', 'cash', 'bank', 'ewallet'])->nullable()->after('name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('accounts', 'type_account')) {
            Schema::table('accounts', function (Blueprint $table) {
                $table->dropColumn('type_account');
            });
        }
    }
};
