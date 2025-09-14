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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
        
            // Identitas kategori
            $table->integer('idpenginput');
            $table->string('name'); // Nama kategori
            $table->string('slug')->unique(); // slug untuk URL
            $table->string('code')->unique()->nullable(); // kode unik kategori (opsional, utk import/export)
        
            // Hierarki (sub-kategori)
            $table->foreignId('parent_id')->nullable()->constrained('categories')->nullOnDelete();
        
            // Informasi tambahan
            $table->text('description')->nullable(); // Deskripsi kategori
            $table->string('icon')->nullable(); // bisa simpan nama file icon/gambar
            $table->string('banner')->nullable(); // banner kategori (misal untuk halaman utama)
            $table->boolean('is_active')->default(true); // status aktif/nonaktif
            $table->integer('sort_order')->default(0); // urutan tampil (kategori bisa diurutkan)
        
            // SEO
            $table->string('meta_title')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->text('meta_description')->nullable();
        
            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        
            $table->timestamps();
        });        
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
