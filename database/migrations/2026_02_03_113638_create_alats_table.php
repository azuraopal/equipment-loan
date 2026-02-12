<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('alats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategoris');
            $table->string('kode_alat', 50)->unique();
            $table->string('nama_alat', 255);
            $table->text('deskripsi')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->integer('stok')->default(0);
            $table->decimal('harga_satuan', 15, 2);
            $table->string('kondisi_awal')->default('Baik');
            $table->string('gambar')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alats');
    }
};
