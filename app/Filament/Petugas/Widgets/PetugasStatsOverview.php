<?php

namespace App\Filament\Petugas\Widgets;

use App\Enums\PeminjamanStatus;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PetugasStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $menunggu = Peminjaman::where('status', PeminjamanStatus::Menunggu)->count();
        $menungguKembali = Peminjaman::where('status', PeminjamanStatus::Menunggu_Verifikasi_Kembali)->count();
        $aktif = Peminjaman::where('status', PeminjamanStatus::Disetujui)->count();
        $kembali = Peminjaman::where('status', PeminjamanStatus::Kembali)->count();
        $totalDenda = Pengembalian::where('status_pembayaran', 'Belum_Lunas')->sum('total_denda');
        $stokRendah = Alat::where('stok', '<=', 2)->count();

        return [
            Stat::make('Menunggu Persetujuan', $menunggu)
                ->description('Peminjaman baru')
                ->descriptionIcon('heroicon-m-clock')
                ->color($menunggu > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-inbox-arrow-down'),

            Stat::make('Verifikasi Pengembalian', $menungguKembali)
                ->description('Perlu diproses')
                ->descriptionIcon('heroicon-m-arrow-uturn-left')
                ->color($menungguKembali > 0 ? 'warning' : 'success')
                ->icon('heroicon-o-arrow-path'),

            Stat::make('Sedang Dipinjam', $aktif)
                ->description('Peminjaman aktif')
                ->color('info')
                ->icon('heroicon-o-document-arrow-up'),

            Stat::make('Selesai Dikembalikan', $kembali)
                ->description('Transaksi selesai')
                ->color('success')
                ->icon('heroicon-o-check-circle'),

            Stat::make('Denda Belum Lunas', 'Rp' . number_format($totalDenda, 0, ',', '.'))
                ->description(Pengembalian::where('status_pembayaran', 'Belum_Lunas')->count() . ' transaksi')
                ->color($totalDenda > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-banknotes'),

            Stat::make('Stok Rendah', $stokRendah . ' alat')
                ->description($stokRendah > 0 ? 'Perlu restok' : 'Semua stok aman')
                ->color($stokRendah > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
