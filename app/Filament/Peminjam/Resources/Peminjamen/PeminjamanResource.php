<?php

namespace App\Filament\Peminjam\Resources\Peminjaman;

use App\Filament\Peminjam\Resources\Peminjaman\Pages\CreatePeminjaman;
use App\Filament\Peminjam\Resources\Peminjaman\Pages\EditPeminjaman;
use App\Filament\Peminjam\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Filament\Peminjam\Resources\Peminjaman\Schemas\PeminjamanForm;
use App\Filament\Peminjam\Resources\Peminjaman\Tables\PeminjamanTable;
use App\Models\Peminjaman;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Peminjaman';

    public static function form(Schema $schema): Schema
    {
        return PeminjamanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PeminjamanTable::configure($table);
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
            'index' => ListPeminjaman::route('/'),
            'create' => CreatePeminjaman::route('/create'),
            'edit' => EditPeminjaman::route('/{record}/edit'),
        ];
    }
}
