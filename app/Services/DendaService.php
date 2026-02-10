<?php

namespace App\Services;

use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Carbon\Carbon;

class DendaService
{
    const DENDA_PER_HARI = 5000;
    const ADMIN_FEE_HILANG = 25000;
    const PERSENTASE_RUSAK = 0.5;

    public static function hitungHariTerlambat(Peminjaman $peminjaman, Carbon $tanggalKembali): int
    {
        $rencana = Carbon::parse($peminjaman->tanggal_kembali_rencana);
        if ($tanggalKembali->lte($rencana)) {
            return 0;
        }
        return (int) $rencana->diffInDays($tanggalKembali);
    }

    public static function hitungDendaTelat(Peminjaman $peminjaman, Carbon $tanggalKembali): float
    {
        $hari = self::hitungHariTerlambat($peminjaman, $tanggalKembali);
        if ($hari <= 0) {
            return 0;
        }

        $dendaTelat = $hari * self::DENDA_PER_HARI;

        $totalNilaiBarang = 0;
        foreach ($peminjaman->peminjamanDetails as $detail) {
            $totalNilaiBarang += $detail->alat->harga_satuan * $detail->jumlah;
        }

        return min($dendaTelat, $totalNilaiBarang);
    }

    public static function hitungDendaItem(string $kondisi, float $hargaSatuan, int $jumlah): float
    {
        return match ($kondisi) {
            'Rusak' => $hargaSatuan * $jumlah * self::PERSENTASE_RUSAK,
            'Hilang' => ($hargaSatuan * $jumlah) + self::ADMIN_FEE_HILANG,
            default => 0,
        };
    }

    public static function cekDendaBelumLunas(int $userId): bool
    {
        return Pengembalian::whereHas('peminjaman', fn($q) => $q->where('user_id', $userId))
            ->where('status_pembayaran', 'Belum_Lunas')
            ->exists();
    }
}
