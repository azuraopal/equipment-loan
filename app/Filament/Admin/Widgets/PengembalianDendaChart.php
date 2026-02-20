<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Pengembalian;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PengembalianDendaChart extends ChartWidget
{
    protected ?string $heading = 'Pengembalian & Denda per Bulan';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '280px';

    protected function getData(): array
    {
        $data = Pengembalian::query()
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as bulan"),
                DB::raw('COUNT(*) as total_pengembalian'),
                DB::raw('COALESCE(SUM(total_denda), 0) as total_denda')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(11)->startOfMonth())
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $labels = [];
        $pengembalianValues = [];
        $dendaValues = [];

        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $key = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');
            $pengembalianValues[] = $data[$key]->total_pengembalian ?? 0;
            $dendaValues[] = (int) ($data[$key]->total_denda ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Pengembalian',
                    'data' => $pengembalianValues,
                    'backgroundColor' => 'rgba(34, 197, 94, 0.7)',
                    'borderColor' => '#22c55e',
                    'borderWidth' => 1,
                ],
                [
                    'label' => 'Total Denda (Rp)',
                    'data' => $dendaValues,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.7)',
                    'borderColor' => '#ef4444',
                    'borderWidth' => 1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
        ];
    }
}
