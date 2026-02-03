<?php

namespace App\Filament\Resources\Alats;

use App\Filament\Resources\Alats\Pages\CreateAlat;
use App\Filament\Resources\Alats\Pages\EditAlat;
use App\Filament\Resources\Alats\Pages\ListAlats;
use App\Filament\Resources\Alats\Schemas\AlatForm;
use App\Filament\Resources\Alats\Tables\AlatsTable;
use App\Models\Alat;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AlatResource extends Resource
{
    protected static ?string $model = Alat::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Alat';

    public static function form(Schema $schema): Schema
    {
        return AlatForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AlatsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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
