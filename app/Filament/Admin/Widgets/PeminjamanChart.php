<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Peminjaman;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PeminjamanChart extends ChartWidget
{
    protected ?string $heading = 'Tren Peminjaman per Bulan';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '400px';
    protected int|string|array $columnSpan = 'full';

    protected function getData(): array
    {
        $data = Peminjaman::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                DB::raw('COUNT(*) as total')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->pluck('total', 'bulan');

        $labels = [];
        $values = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $values[] = $data[$key] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Peminjaman',
                    'data' => $values,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
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
