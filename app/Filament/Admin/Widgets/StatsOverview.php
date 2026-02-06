<?php

namespace App\Filament\Admin\Widgets;

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
        return [
            Stat::make('Total User', User::count())
                ->description('Pengguna terdaftar')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->icon('heroicon-o-users'),

            Stat::make('Total Alat', Alat::count())
                ->description('Aset tersedia')
                ->color('primary')
                ->icon('heroicon-o-rectangle-stack'),

            Stat::make('Total Kategori', Kategori::count())
                ->description('Jenis Kategori')
                ->color('info')
                ->icon('heroicon-o-tag'),

            Stat::make('Peminjaman', Peminjaman::count())
                ->description('Total transaksi pinjam')
                ->color('warning')
                ->icon('heroicon-o-document-arrow-up'),

            Stat::make('Pengembalian', Pengembalian::count())
                ->description('Total transaksi kembali')
                ->color('danger')
                ->icon('heroicon-o-document-check'),
        ];
    }
}