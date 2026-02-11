<?php

namespace App\Filament\Petugas\Widgets;

use Filament\Widgets\Widget;

class LaporanWidget extends Widget
{
    protected string $view = 'filament.petugas.widgets.laporan-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public ?string $dari = null;
    public ?string $sampai = null;

    public function mount(): void
    {
        $this->dari = now()->startOfMonth()->format('Y-m-d');
        $this->sampai = now()->format('Y-m-d');
    }

    public function buildUrl(string $route): string
    {
        $params = [];
        if ($this->dari) {
            $params['dari'] = $this->dari;
        }
        if ($this->sampai) {
            $params['sampai'] = $this->sampai;
        }
        return route($route, $params);
    }
}
