<?php

namespace App\Filament\Peminjam\Resources\Pengembalian;

use App\Filament\Peminjam\Resources\Pengembalian\Pages\CreatePengembalian;
use App\Filament\Peminjam\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Auth;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationLabel = 'Kembalikan Alat';
    protected static ?string $pluralModelLabel = 'Kembalikan Alat';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Select::make('peminjaman_id')
                    ->label('Pilih Peminjaman')
                    ->options(function () {

                        return Peminjaman::where('user_id', Auth::id())
                            ->where('status', 'Disetujui')
                            ->whereDoesntHave('pengembalian')
                            ->pluck('nomor_peminjaman', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('tanggal_kembali_real')
                    ->label('Tanggal Pengembalian')
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->maxDate(now()),

                Hidden::make('nomor_pengembalian')
                    ->default(fn() => 'KEM-' . strtoupper(uniqid())),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('peminjaman', function ($q) {
                $q->where('user_id', Auth::id());
            }))
            ->columns([
                TextColumn::make('nomor_pengembalian'),
                TextColumn::make('peminjaman.nomor_peminjaman')->label('No. Pinjam'),
                TextColumn::make('tanggal_kembali_real')->date(),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'warning',
                    }),
                TextColumn::make('total_denda')->money('IDR'),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('bayar_denda')
                    ->label('Bayar Denda')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('danger')
                    ->url(fn(Pengembalian $record) => route('payment.show', $record))
                    ->openUrlInNewTab(false)
                    ->visible(fn(Pengembalian $record) => $record->status_pembayaran === 'Belum_Lunas' && $record->total_denda > 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'create' => CreatePengembalian::route('/create'),
        ];
    }
}