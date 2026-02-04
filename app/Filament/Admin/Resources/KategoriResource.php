<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Kategoris\Pages\CreateKategori;
use App\Filament\Admin\Resources\Kategoris\Pages\EditKategori;
use App\Filament\Admin\Resources\Kategoris\Pages\ListKategori;
use App\Models\Kategori;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;

class KategoriResource extends Resource
{
    protected static ?string $model = Kategori::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_kategori')
                    ->required()
                    ->maxLength(255),
                Textarea::make('deskripsi')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama_kategori')->searchable(),
                TextColumn::make('alats_count')
                    ->counts('alats')
                    ->label('Jumlah Alat'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKategori::route('/'),
            'create' => CreateKategori::route('/path: create'),
            'edit' => EditKategori::route('/{record}/edit'),
        ];
    }
}