<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\Widget;

class LaporanWidget extends Widget
{
    protected string $view = 'filament.admin.widgets.laporan-widget';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';
}
