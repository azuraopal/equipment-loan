<?php

namespace App\Http\Controllers;

use App\Models\Alat;
use App\Models\Peminjaman;
use App\Enums\PeminjamanStatus;
use App\Services\QrCodeService;

class AlatInfoController extends Controller
{
    public function alat(string $kode_alat)
    {
        $alat = Alat::where('kode_alat', $kode_alat)->with('kategori')->firstOrFail();

        $sedangDipinjam = $alat->peminjamans()
            ->whereIn('status', [
                PeminjamanStatus::Disetujui->value,
                PeminjamanStatus::Menunggu_Verifikasi_Kembali->value,
            ])
            ->count();

        $qrCode = QrCodeService::generateSvg(url("/alat/info/{$alat->kode_alat}"));

        return view('public.alat-info', compact('alat', 'sedangDipinjam', 'qrCode'));
    }

    public function peminjaman(string $nomor)
    {
        $peminjaman = Peminjaman::where('nomor_peminjaman', $nomor)
            ->with(['user', 'peminjamanDetails.alat', 'pengembalian'])
            ->firstOrFail();

        $qrCode = QrCodeService::generateSvg(url("/peminjaman/info/{$peminjaman->nomor_peminjaman}"));

        return view('public.peminjaman-info', compact('peminjaman', 'qrCode'));
    }
}

