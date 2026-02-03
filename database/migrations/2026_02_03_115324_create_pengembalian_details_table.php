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
        Schema::create('pengembalian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pengembalian_id')->constrained('pengembalians')->cascadeOnDelete();
            $table->foreignId('alat_id')->constrained('alats');
            $table->integer('jumlah_kembali');
            $table->string('kondisi_kembali');
            $table->text('catatan_kondisi')->nullable();
            $table->decimal('denda_item', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalian_details');
    }
};
