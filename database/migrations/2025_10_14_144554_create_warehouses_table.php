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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama gudang
            $table->string('code')->unique()->nullable(); // Kode gudang (contoh: GUD001)
            $table->string('address')->nullable(); // Alamat lengkap
            $table->string('city')->nullable(); // Kota
            $table->string('phone')->nullable(); // Telepon
            $table->string('pic_name')->nullable(); // Penanggung jawab
            $table->string('pic_contact')->nullable(); // Kontak penanggung jawab
            $table->enum('type', ['store', 'warehouse'])->default('warehouse');
            $table->integer('idpenginput'); // ID UMKM owner
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
