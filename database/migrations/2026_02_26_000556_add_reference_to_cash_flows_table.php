<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('description');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');

            $table->index(['reference_type','reference_id']);
        });
    }

    public function down()
    {
        Schema::table('cash_flows', function (Blueprint $table) {
            $table->dropIndex(['reference_type','reference_id']);
            $table->dropColumn(['reference_type','reference_id']);
        });
    }
};
