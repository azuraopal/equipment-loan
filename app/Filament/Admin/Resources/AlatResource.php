<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Alats\Pages\CreateAlat;
use App\Filament\Admin\Resources\Alats\Pages\EditAlat;
use App\Filament\Admin\Resources\Alats\Pages\ListAlats;
use App\Models\Alat;
use App\Models\Kategori;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use BackedEnum;
use UnitEnum;

class AlatResource extends Resource
{
    protected static ?string $model = Alat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([ 
                Select::make('kategori_id')
                    ->label('Kategori')
                    ->options(Kategori::all()->pluck('nama_kategori', 'id'))
                    ->required(),
                TextInput::make('nama_alat')
                    ->required()
                    ->maxLength(255),
                TextInput::make('kode_alat')
                    ->required()
                    ->unique(ignoreRecord: true),
                TextInput::make('stok')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('harga_satuan')
                    ->numeric()
                    ->prefix('Rp'),
                Select::make('kondisi_awal')
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak' => 'Rusak',
                        'Hilang' => 'Hilang',
                    ])
                    ->default('Baik'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_alat')->searchable(),
                TextColumn::make('kode_alat')->searchable(),
                TextColumn::make('kategori.nama_kategori')->badge(),
                TextColumn::make('stok')->label('Stok'),
                TextColumn::make('kondisi_awal')->badge(),
            ])
            ->actions([
                \Filament\Actions\EditAction::make(),
                \Filament\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlats::route('/'),
            'create' => CreateAlat::route('/create'),
            'edit' => EditAlat::route('/{record}/edit'),
        ];
    }
}