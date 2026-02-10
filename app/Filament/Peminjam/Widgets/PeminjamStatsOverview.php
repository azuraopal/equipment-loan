<?php

namespace App\Filament\Peminjam\Widgets;

use App\Enums\PeminjamanStatus;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PeminjamStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userId = auth()->id();

        $totalPinjam = Peminjaman::where('user_id', $userId)->count();
        $aktif = Peminjaman::where('user_id', $userId)
            ->whereIn('status', [
                PeminjamanStatus::Disetujui,
                PeminjamanStatus::Menunggu_Verifikasi_Kembali,
            ])
            ->count();
        $selesai = Peminjaman::where('user_id', $userId)
            ->where('status', PeminjamanStatus::Kembali)
            ->count();
        $menunggu = Peminjaman::where('user_id', $userId)
            ->where('status', PeminjamanStatus::Menunggu)
            ->count();
        $ditolak = Peminjaman::where('user_id', $userId)
            ->where('status', PeminjamanStatus::Ditolak)
            ->count();

        $totalDenda = Pengembalian::whereHas('peminjaman', fn($q) => $q->where('user_id', $userId))
            ->where('status_pembayaran', 'Belum_Lunas')
            ->sum('total_denda');

        return [
            Stat::make('Total Pinjaman', $totalPinjam)
                ->description('Riwayat peminjaman saya')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Sedang Aktif', $aktif)
                ->description('Pinjaman berjalan')
                ->icon('heroicon-o-arrow-path')
                ->color($aktif > 0 ? 'warning' : 'success'),

            Stat::make('Menunggu Proses', $menunggu)
                ->description('Menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color($menunggu > 0 ? 'info' : 'success'),

            Stat::make('Selesai Dikembalikan', $selesai)
                ->description('Sudah kembali')
                ->icon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Ditolak', $ditolak)
                ->description('Peminjaman ditolak')
                ->icon('heroicon-o-x-circle')
                ->color($ditolak > 0 ? 'danger' : 'gray'),

            Stat::make('Denda Belum Lunas', 'Rp' . number_format($totalDenda, 0, ',', '.'))
                ->description($totalDenda > 0 ? 'Segera lunasi' : 'Tidak ada denda')
                ->icon('heroicon-o-banknotes')
                ->color($totalDenda > 0 ? 'danger' : 'success'),
        ];
    }
}
