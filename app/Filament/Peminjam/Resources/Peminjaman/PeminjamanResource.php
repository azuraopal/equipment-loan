<?php

namespace App\Filament\Peminjam\Resources\Peminjaman;

use App\Filament\Peminjam\Resources\Peminjaman\Pages\CreatePeminjaman;
use App\Filament\Peminjam\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Models\Peminjaman;
use App\Models\Alat;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Ajukan Peminjaman';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Detail Waktu')
                    ->schema([
                        DatePicker::make('tanggal_pinjam')
                            ->default(now())
                            ->required()
                            ->minDate(now()->startOfDay()),
                        DatePicker::make('tanggal_kembali_rencana')
                            ->label('Rencana Kembali')
                            ->required()
                            ->after('tanggal_pinjam'),
                        Textarea::make('keperluan')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Pilih Alat')
                    ->schema([
                        Repeater::make('peminjamanDetails')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->label('Alat')
                                    ->options(Alat::where('stok', '>', 0)->pluck('nama_alat', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->reactive()
                                    ->afterStateUpdated(
                                        fn($state, Set $set) =>
                                        $set('max_stok', Alat::find($state)?->stok ?? 0)
                                    ),

                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn(Get $get) => $get('max_stok') ?? 100)
                                    ->required(),

                                Hidden::make('max_stok'),
                            ])
                            ->minItems(1)
                            ->addActionLabel('Tambah Alat Lain'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_peminjaman'),
                TextColumn::make('tanggal_pinjam')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeminjaman::route('/'),
            'create' => CreatePeminjaman::route('/create')
        ];
    }
}