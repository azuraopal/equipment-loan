<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\PeminjamanStatus;
use App\Models\PeminjamanAlat;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopAlatChart extends ChartWidget
{
    protected ?string $heading = 'Top 5 Alat Paling Sering Dipinjam';
    protected static ?int $sort = 4;
    protected int|string|array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $data = PeminjamanAlat::query()
            ->join('peminjamans', 'peminjaman_alats.peminjaman_id', '=', 'peminjamans.id')
            ->join('alats', 'peminjaman_alats.alat_id', '=', 'alats.id')
            ->whereIn('peminjamans.status', [
                PeminjamanStatus::Disetujui,
                PeminjamanStatus::Menunggu_Verifikasi_Kembali,
                PeminjamanStatus::Kembali
            ])
            ->select('alats.nama_alat', DB::raw('SUM(peminjaman_alats.jumlah) as total_peminjaman'))
            ->groupBy('peminjaman_alats.alat_id', 'alats.nama_alat')
            ->orderByDesc('total_peminjaman')
            ->limit(5)
            ->pluck('total_peminjaman', 'nama_alat');

        return [
            'datasets' => [
                [
                    'label' => 'Total Dipinjam',
                    'data' => $data->values()->toArray(),
                    'backgroundColor' => [
                        '#ef4444',
                        '#f97316',
                        '#f59e0b',
                        '#10b981',
                        '#3b82f6',
                    ],
                ],
            ],
            'labels' => $data->keys()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
