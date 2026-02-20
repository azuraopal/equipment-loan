<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Pengembalian;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DendaTrenChart extends ChartWidget
{
    protected ?string $heading = 'Tren Pendapatan Denda per Bulan';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $lunas = Pengembalian::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                DB::raw('COALESCE(SUM(total_denda), 0) as total')
            )
            ->where('status_pembayaran', 'Lunas')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $belumLunas = Pengembalian::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                DB::raw('COALESCE(SUM(total_denda), 0) as total')
            )
            ->where('status_pembayaran', 'Belum_Lunas')
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('bulan')
            ->pluck('total', 'bulan');

        $labels = [];
        $lunasValues = [];
        $belumLunasValues = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $lunasValues[] = (int) ($lunas[$key] ?? 0);
            $belumLunasValues[] = (int) ($belumLunas[$key] ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Lunas (Rp)',
                    'data' => $lunasValues,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Belum Lunas (Rp)',
                    'data' => $belumLunasValues,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }
}
