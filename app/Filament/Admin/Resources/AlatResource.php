<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Alats\Pages\CreateAlat;
use App\Filament\Admin\Resources\Alats\Pages\EditAlat;
use App\Filament\Admin\Resources\Alats\Pages\ListAlats;
use App\Models\Alat;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
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
                FileUpload::make('gambar')
                    ->directory('')
                    ->visibility('private')
                    ->image()
                    ->imagePreviewHeight('300')
                    ->columnSpanFull()
                    ->required(),
                TextInput::make('nama_alat')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                Select::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('kode_alat')
                    ->placeholder('Otomatis oleh sistem')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                TextInput::make('stok')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('harga_satuan')
                    ->numeric()
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->stripCharacters('.')
                    ->numeric()
                    ->prefix('Rp'),
                Select::make('kondisi_awal')
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak' => 'Rusak',
                        'Hilang' => 'Hilang',
                    ])
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->default('Baik')
                    ->required(),
                Textarea::make('spesifikasi')
                    ->maxLength('500')
                    ->columnSpan(2),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar'),
                TextColumn::make('nama_alat')->searchable(),
                TextColumn::make('kode_alat')->searchable(),
                TextColumn::make('kategori.nama_kategori')->badge(),
                TextColumn::make('stok')->label('Stok'),
                TextColumn::make('kondisi_awal')->badge(),
                TextColumn::make('spesifikasi'),
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