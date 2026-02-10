<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\PeminjamanStatus;
use App\Models\Alat;
use App\Models\Kategori;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalPeminjaman = Peminjaman::count();
        $aktif = Peminjaman::where('status', PeminjamanStatus::Disetujui)->count();
        $menunggu = Peminjaman::where('status', PeminjamanStatus::Menunggu)->count();
        $kembali = Peminjaman::where('status', PeminjamanStatus::Kembali)->count();
        $totalDenda = Pengembalian::where('status_pembayaran', 'Belum_Lunas')->sum('total_denda');
        $stokRendah = Alat::where('stok', '<=', 2)->count();

        return [
            Stat::make('Total User', User::count())
                ->description('Pengguna terdaftar')
                ->descriptionIcon('heroicon-m-users')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Total Alat', Alat::count())
                ->description(Alat::sum('stok') . ' unit tersedia')
                ->color('primary')
                ->icon('heroicon-o-cube'),

            Stat::make('Total Kategori', Kategori::count())
                ->description('Jenis kategori')
                ->color('info')
                ->icon('heroicon-o-tag'),

            Stat::make('Total Peminjaman', $totalPeminjaman)
                ->description($aktif . ' aktif · ' . $menunggu . ' menunggu')
                ->color('warning')
                ->icon('heroicon-o-document-arrow-up'),

            Stat::make('Total Pengembalian', Pengembalian::count())
                ->description($kembali . ' selesai kembali')
                ->color('success')
                ->icon('heroicon-o-document-check'),

            Stat::make('Denda Belum Lunas', 'Rp' . number_format($totalDenda, 0, ',', '.'))
                ->description(Pengembalian::where('status_pembayaran', 'Belum_Lunas')->count() . ' transaksi')
                ->color($totalDenda > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-banknotes'),
        ];
    }
}