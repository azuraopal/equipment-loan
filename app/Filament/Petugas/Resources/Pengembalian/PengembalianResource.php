<?php

namespace App\Filament\Petugas\Resources\Pengembalian;

use App\Filament\Petugas\Resources\Pengembalian\Pages\EditPengembalian;
use App\Filament\Petugas\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Pengembalian;
use Auth;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Verifikasi Pengembalian';
    protected static ?string $pluralModelLabel = 'Verifikasi Pengembalian';

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
                Section::make('Verifikasi Petugas')
                    ->schema([
                        Select::make('petugas_id')
                            ->label('Diterima Oleh')
                            ->relationship('petugas', 'name')
                            ->default(Auth::id())
                            ->disabled()
                            ->dehydrated(),

                        DatePicker::make('tanggal_kembali_real')
                            ->label('Tanggal Terima Fisik')
                            ->required(),
                    ])->columns(2),

                Section::make('Cek Kondisi Barang')
                    ->description('Sesuaikan kondisi barang dengan fisik yang diterima.')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->relationship('alat', 'nama_alat')
                                    ->required()
                                    ->searchable(),
                                TextInput::make('jumlah_kembali')
                                    ->numeric()
                                    ->required()
                                    ->default(1),
                                Select::make('kondisi_kembali')
                                    ->options([
                                        'Baik' => 'Baik',
                                        'Rusak' => 'Rusak',
                                        'Hilang' => 'Hilang',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function (Get $get, Set $set, $state) {
                                        if ($state === 'Baik') {
                                            $set('denda_item', 0);
                                        }
                                    }),
                                TextInput::make('denda_item')
                                    ->label('Denda Kerusakan/Hilang')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->disabled(fn(Get $get) => $get('kondisi_kembali') === 'Baik')
                                    ->dehydrated(),
                            ])
                            ->columns(2)
                            ->addActionLabel('Tambah Barang Kembali'),
                    ]),

                Section::make('Kalkulasi Pembayaran')
                    ->schema([
                        TextInput::make('denda_keterlambatan')
                            ->label('Denda Keterlambatan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),

                        TextInput::make('total_denda')
                            ->label('Total Harus Dibayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->helperText('Jumlahkan Denda Keterlambatan + Total Denda Item'),

                        Select::make('status_pembayaran')
                            ->options([
                                'Belum_Lunas' => 'Belum Lunas',
                                'Lunas' => 'Lunas',
                            ])
                            ->required()
                            ->default('Belum_Lunas'),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengembalian')->searchable()->sortable(),
                TextColumn::make('peminjaman.user.name')->label('Peminjam')->searchable(),
                TextColumn::make('tanggal_kembali_real')->date(),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('total_denda')->money('IDR'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()->label('Verifikasi'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'edit' => EditPengembalian::route('/{record}/create'),
        ];
    }
}