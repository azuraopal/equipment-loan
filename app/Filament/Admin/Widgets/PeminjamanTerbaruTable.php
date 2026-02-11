<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Peminjaman;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class PeminjamanTerbaruTable extends BaseWidget
{
    protected static ?string $heading = 'Peminjaman Terbaru';
    protected static ?int $sort = 7;
    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                fn() => Peminjaman::query()
                    ->with('user')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('nomor_peminjaman')
                    ->label('No. Peminjaman')
                    ->searchable(false)
                    ->sortable(false)
                    ->weight('bold'),

                TextColumn::make('user.name')
                    ->label('Peminjam')
                    ->searchable(false)
                    ->sortable(false),

                TextColumn::make('tanggal_pinjam')
                    ->label('Tgl Pinjam')
                    ->date('d M Y')
                    ->sortable(false),

                TextColumn::make('tanggal_kembali_rencana')
                    ->label('Tgl Kembali Rencana')
                    ->date('d M Y')
                    ->sortable(false),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(false),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(false),
            ])
            ->paginated(false);
    }
}
