<?php

namespace App\Filament\Petugas\Widgets;

use Filament\Widgets\Widget;

class LaporanWidget extends Widget
{
    protected string $view = 'filament.petugas.widgets.laporan-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';
}
