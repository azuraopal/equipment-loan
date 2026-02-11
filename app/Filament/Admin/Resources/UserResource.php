<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\UsersResource\Pages\CreateUser;
use App\Filament\Admin\Resources\UsersResource\Pages\EditUser;
use App\Filament\Admin\Resources\UsersResource\Pages\ListUsers;
use App\Models\User;
use App\Enums\UserRole;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use UnitEnum;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')->required(),
                TextInput::make('email')->email()->required(),
                TextInput::make('password')
                    ->password()
                    ->dehydrated(fn($state) => filled($state))
                    ->required(fn(string $context) => $context === 'create'),
                Select::make('role')
                    ->options(UserRole::class)
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email'),
                TextColumn::make('is_active')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        '1', 'true' => 'success',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        '1', 'true' => 'Aktif',
                        default => 'Nonaktif',
                    }),
                TextColumn::make('role')->badge(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}