<?php

namespace App\Filament\Admin\Resources\LogAktivitas;

use App\Filament\Admin\Resources\LogAktivitas\Pages\ManageLogAktivitas;
use App\Models\LogAktivitas;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LogAktivitasResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'LogAktivitas';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('LogAktivitas')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('LogAktivitas')
            ->columns([
                TextColumn::make('LogAktivitas')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogAktivitas::route('/'),
        ];
    }
}
