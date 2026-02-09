<?php

namespace App\Filament\Admin\Resources; 

use App\Filament\Admin\Resources\LogAktivitas\Pages\ManageLogAktivitas;

use App\Models\LogAktivitas;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;
use BackedEnum;

class LogAktivitasResource extends Resource
{
    protected static ?string $model = LogAktivitas::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Log Aktivitas';
    protected static ?string $pluralModelLabel = 'Log Aktivitas';

    protected static string|UnitEnum|null $navigationGroup = 'Sistem';

    // public static function canCreate(): bool
    // {
    //     return false;
    // }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System / Guest')
                    ->searchable(),

                TextColumn::make('jenis_aktivitas')
                    ->label('Aksi')
                    ->badge()
                    ->sortable(),

                TextColumn::make('model')
                    ->label('Modul')
                    ->searchable(),

                TextColumn::make('deskripsi')
                    ->label('Deskripsi')
                    ->limit(50),

                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('jenis_aktivitas')
                    ->options([
                        'INSERT' => 'Insert',
                        'UPDATE' => 'Update',
                        'DELETE' => 'Delete',
                        'LOGIN' => 'Login',
                        'LOGOUT' => 'Logout',
                        'APPROVE' => 'Approve',
                        'REJECT' => 'Reject',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading('Detail Log Aktivitas')
                    ->infolist([
                        TextEntry::make('jenis_aktivitas')->label('Aksi'),
                        TextEntry::make('model')->label('Modul'),
                        TextEntry::make('deskripsi')->label('Deskripsi'),
                        TextEntry::make('ip_address')->label('IP Address'),
                        TextEntry::make('user_agent')->label('User Agent'),
                        KeyValueEntry::make('data_lama')->label('Data Sebelumnya'),
                        KeyValueEntry::make('data_baru')->label('Data Sesudahnya'),
                        TextEntry::make('created_at')
                            ->label('Waktu')
                            ->dateTime(),
                    ]),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLogAktivitas::route('/'),
        ];
    }
}