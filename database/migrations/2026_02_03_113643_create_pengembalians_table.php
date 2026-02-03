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
        Schema::create('pengembalians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamans');
            $table->foreignId('petugas_id')->constrained('users');
            $table->string('nomor_pengembalian', 50)->unique();
            $table->date('tanggal_kembali_real');
            $table->integer('hari_terlambat')->default(0);
            $table->decimal('denda_keterlambatan', 15, 2)->default(0);
            $table->decimal('denda_kerusakan', 15, 2)->default(0);
            $table->decimal('denda_kehilangan', 15, 2)->default(0);
            $table->decimal('total_denda', 15, 2)->default(0);
            $table->string('status_pembayaran')->default('Belum_Lunas');
            $table->date('tanggal_bayar')->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengembalians');
    }
};
