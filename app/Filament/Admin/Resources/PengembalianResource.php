<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Pengembalian\Pages\CreatePengembalian;
use App\Filament\Admin\Resources\Pengembalian\Pages\EditPengembalian;
use App\Filament\Admin\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Pengembalian;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Riwayat Pengembalian';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?string $pluralModelLabel = 'Pengembalian';

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
                Section::make('Data Utama')
                    ->schema([
                        Select::make('peminjaman_id')
                            ->relationship('peminjaman', 'nomor_peminjaman')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        TextInput::make('nomor_pengembalian')
                            ->required()
                            ->unique(ignoreRecord: true),
                        DatePicker::make('tanggal_kembali_real')
                            ->native(false)
                            ->required(),
                        Select::make('petugas_id')
                            ->relationship('petugas', 'name')
                            ->required(),
                    ])->columns(2),

                Section::make('Detail Barang & Kondisi')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->relationship('alat', 'nama_alat')
                                    ->required()
                                    ->searchable()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(), // Mencegah duplikasi alat
                                TextInput::make('jumlah_kembali')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                Select::make('kondisi_kembali')
                                    ->options([
                                        'Baik' => 'Baik',
                                        'Rusak' => 'Rusak',
                                        'Hilang' => 'Hilang',
                                    ])
                                    ->required(),
                                TextInput::make('denda_item')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),
                                Textarea::make('catatan_kondisi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Alat'),
                    ]),

                Section::make('Total Denda & Pembayaran')
                    ->schema([
                        TextInput::make('denda_keterlambatan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('total_denda')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('status_pembayaran')
                            ->options([
                                'Belum_Lunas' => 'Belum Lunas',
                                'Lunas' => 'Lunas',
                            ])
                            ->default('Belum_Lunas'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengembalian')->searchable()->sortable(),
                TextColumn::make('peminjaman.nomor_peminjaman')->label('No. Pinjam')->searchable(),
                TextColumn::make('peminjaman.user.name')->label('Peminjam')->searchable(),
                TextColumn::make('tanggal_kembali_real')->date()->label('Tgl Kembali'),
                TextColumn::make('total_denda')->money('IDR'),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'create' => CreatePengembalian::route('/create'),
            'edit' => EditPengembalian::route('/{record}/edit'),
        ];
    }
}