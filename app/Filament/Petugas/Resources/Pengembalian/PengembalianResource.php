<?php

namespace App\Filament\Petugas\Resources\Pengembalian;

use App\Filament\Petugas\Resources\Pengembalian\Pages\EditPengembalian;
use App\Filament\Petugas\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Alat;
use App\Models\Pengembalian;
use Auth;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
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
        return true;
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
                            ->required()
                            ->default(now()),
                    ])->columns(2),

                Section::make('Cek Kondisi Barang')
                    ->description('Sistem akan menghitung denda otomatis berdasarkan kondisi (Hilang=100%, Rusak=50%).')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->live()
                            ->schema([
                                Select::make('alat_id')
                                    ->relationship('alat', 'nama_alat')
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $alat = Alat::find($state);
                                        $harga = $alat ? $alat->harga_satuan : 0;

                                        $set('harga_temp', $harga);

                                        self::hitungDendaPerItem($set, $get);
                                    }),

                                Hidden::make('harga_temp')
                                    ->default(0)
                                    ->dehydrated(false),

                                TextInput::make('jumlah_kembali')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->reactive()
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungDendaPerItem($set, $get)),

                                Select::make('kondisi_kembali')
                                    ->options([
                                        'Baik' => 'Baik (Denda 0)',
                                        'Rusak' => 'Rusak (Denda 50%)',
                                        'Hilang' => 'Hilang (Denda 100%)',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungDendaPerItem($set, $get)),

                                TextInput::make('denda_item')
                                    ->label('Denda (Auto)')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes(['style' => 'background-color: #f3f4f6;']),
                            ])
                            ->columns(2)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungGrandTotal($set, $get)),
                    ]),

                Section::make('Kalkulasi Pembayaran')
                    ->schema([
                        TextInput::make('denda_keterlambatan')
                            ->label('Denda Keterlambatan')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungGrandTotal($set, $get)),

                        TextInput::make('total_denda')
                            ->label('Total Harus Dibayar')
                            ->numeric()
                            ->prefix('Rp')
                            ->required()
                            ->readOnly()
                            ->default(0)
                            ->helperText('Otomatis: Denda Keterlambatan + Total Denda Item')
                            ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.1em; background-color: #ecfdf5; color: #065f46;']),

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
                TextColumn::make('tanggal_kembali_real')->date()->label('Tgl Kembali'),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('total_denda')->money('IDR')->label('Total Tagihan'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make()->label('Verifikasi'),
            ]);
    }

    public static function hitungDendaPerItem(Set $set, Get $get)
    {
        $harga = (float) $get('harga_temp');
        if ($harga == 0 && $get('alat_id')) {
            $alat = Alat::find($get('alat_id'));
            $harga = $alat ? $alat->harga_satuan : 0;
            $set('harga_temp', $harga);
        }

        $jumlah = (int) $get('jumlah_kembali');
        $kondisi = $get('kondisi_kembali');

        $denda = 0;

        if ($kondisi === 'Hilang') {
            $denda = $harga * $jumlah * 1.0;
        } elseif ($kondisi === 'Rusak') {
            $denda = $harga * $jumlah * 0.5;
        } else {
            $denda = 0;
        }

        $set('denda_item', $denda);
    }

    public static function hitungGrandTotal(Set $set, Get $get)
    {
        $items = $get('details');
        $totalDendaItem = 0;

        if (is_array($items)) {
            foreach ($items as $item) {
                $totalDendaItem += (float) ($item['denda_item'] ?? 0);
            }
        }

        $dendaTelat = (float) $get('denda_keterlambatan');

        $set('total_denda', $totalDendaItem + $dendaTelat);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'edit' => EditPengembalian::route('/{record}/create'),
        ];
    }
}