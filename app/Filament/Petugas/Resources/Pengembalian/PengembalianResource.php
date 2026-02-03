<?php

namespace App\Filament\Petugas\Resources\Pengembalian;

use App\Filament\Petugas\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Pengembalian;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Riwayat Pengembalian';
    protected static ?string $pluralModelLabel = 'Riwayat Pengembalian';

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
            ->components([
                Section::make('Info Pengembalian')
                    ->schema([
                        TextInput::make('nomor_pengembalian')
                            ->label('No. Kembali'),

                        TextInput::make('peminjaman.user.name')
                            ->label('Peminjam'),

                        DatePicker::make('tanggal_kembali_real')
                            ->label('Tgl Kembali'),

                        TextInput::make('petugas.name')
                            ->label('Petugas Penerima'),
                    ])->columns(2),

                Section::make('Rincian Denda')
                    ->schema([
                        TextInput::make('denda_keterlambatan')
                            ->prefix('Rp')
                            ->numeric(),

                        TextInput::make('denda_kerusakan')
                            ->label('Denda Rusak/Hilang')
                            ->prefix('Rp')
                            ->numeric(),

                        TextInput::make('total_denda')
                            ->prefix('Rp')
                            ->numeric()
                            ->extraInputAttributes(['style' => 'font-weight:bold']),

                        TextInput::make('status_pembayaran')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Lunas' => 'success',
                                'Belum_Lunas' => 'danger',
                                default => 'gray',
                            }),
                    ])->columns(2),

                Section::make('Barang Dikembalikan')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                TextInput::make('alat.nama_alat')
                                    ->label('Nama Alat'),
                                TextInput::make('jumlah_kembali')
                                    ->label('Qty'),
                                TextInput::make('kondisi_kembali')
                                    ->label('Kondisi'),
                            ])
                            ->addable(false)
                            ->deletable(false)
                            ->editable(false)
                            ->columnSpanFull(),
                    ]),

                Textarea::make('catatan_pengembalian')
                    ->label('Catatan Kerusakan/Kname: ehilangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengembalian')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('peminjaman.user.name')
                    ->label('Peminjam')
                    ->searchable(),

                TextColumn::make('tanggal_kembali_real')
                    ->date()
                    ->label('Tgl Kembali')
                    ->sortable(),

                TextColumn::make('total_denda')
                    ->money('IDR')
                    ->label('Total Denda')
                    ->sortable(),

                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum_Lunas' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('petugas.name')
                    ->label('Petugas')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
        ];
    }
}