<?php

namespace App\Filament\Petugas\Pages;

use App\Models\Alat;
use App\Models\Peminjaman;
use Filament\Pages\Page;
use BackedEnum;

class ScanQrPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-qr-code';

    protected static ?string $navigationLabel = 'Scan QR Code';

    protected static ?string $title = 'Scan QR Code';

    protected static ?string $slug = 'scan-qr';

    protected static ?int $navigationSort = 0;

    protected string $view = 'filament.petugas.pages.scan-qr';

    public ?string $scanResult = null;
    public ?array $alatInfo = null;
    public ?array $peminjamanInfo = null;
    public ?string $errorMessage = null;

    public function processScanResult(string $result): void
    {
        $this->scanResult = $result;
        $this->alatInfo = null;
        $this->peminjamanInfo = null;
        $this->errorMessage = null;

        if (preg_match('/\/alat\/info\/([A-Za-z0-9\-]+)/', $result, $matches)) {
            $kode = $matches[1];
            $alat = Alat::where('kode_alat', $kode)->with('kategori')->first();
            if ($alat) {
                $sedangDipinjam = $alat->peminjamans()
                    ->whereIn('status', ['Disetujui', 'Menunggu_Verifikasi_Kembali'])
                    ->count();

                $this->alatInfo = [
                    'id' => $alat->id,
                    'kode_alat' => $alat->kode_alat,
                    'nama_alat' => $alat->nama_alat,
                    'kategori' => $alat->kategori->nama_kategori ?? '-',
                    'stok' => $alat->stok,
                    'kondisi' => $alat->kondisi_awal,
                    'harga' => 'Rp ' . number_format($alat->harga_satuan, 0, ',', '.'),
                    'gambar' => $alat->gambar ? asset('storage/' . $alat->gambar) : null,
                    'sedang_dipinjam' => $sedangDipinjam,
                    'url' => url("/alat/info/{$alat->kode_alat}"),
                ];
                return;
            }
        }

        if (preg_match('/\/peminjaman\/info\/([A-Za-z0-9\-]+)/', $result, $matches)) {
            $nomor = $matches[1];
            $peminjaman = Peminjaman::where('nomor_peminjaman', $nomor)
                ->with(['user', 'peminjamanDetails.alat'])
                ->first();

            if ($peminjaman) {
                $items = $peminjaman->peminjamanDetails->map(fn($d) => [
                    'nama' => $d->alat->nama_alat,
                    'jumlah' => $d->jumlah,
                ])->toArray();

                $this->peminjamanInfo = [
                    'nomor' => $peminjaman->nomor_peminjaman,
                    'peminjam' => $peminjaman->user->name,
                    'status' => $peminjaman->status->getLabel(),
                    'status_color' => $peminjaman->status->getColor(),
                    'tanggal_pinjam' => $peminjaman->tanggal_pinjam->format('d M Y'),
                    'tanggal_kembali' => $peminjaman->tanggal_kembali_rencana->format('d M Y'),
                    'items' => $items,
                    'url' => url("/peminjaman/info/{$peminjaman->nomor_peminjaman}"),
                ];
                return;
            }
        }

        $this->errorMessage = 'QR Code tidak dikenali. Pastikan Anda scan QR dari sistem ini.';
    }
}
