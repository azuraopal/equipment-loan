<?php

namespace App\Filament\Peminjam\Resources\Peminjaman;

use App\Filament\Peminjam\Resources\Peminjaman\Pages\CreatePeminjaman;
use App\Filament\Peminjam\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Models\Peminjaman;
use App\Models\Alat;
use App\Enums\PeminjamanStatus;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use Filament\Notifications\Notification;
use BackedEnum;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;
    protected static ?string $slug = 'peminjaman';
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Peminjaman Saya';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Form Pengajuan')
                    ->schema([
                        DatePicker::make('tanggal_pinjam')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->minDate(now()->startOfDay()),
                        DatePicker::make('tanggal_kembali_rencana')
                            ->label('Rencana Kembali')
                            ->required()
                            ->native(false)
                            ->after('tanggal_pinjam'),
                        Textarea::make('keperluan')
                            ->required(),
                    ]),
                Section::make('Barang')
                    ->schema([
                        Repeater::make('peminjamanDetails')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->label('Alat')
                                    ->options(Alat::where('stok', '>', 0)->pluck('nama_alat', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('jumlah')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required(),
                            ])
                            ->minItems(1)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('user_id', auth()->id()))
            ->columns([
                TextColumn::make('nomor_peminjaman')
                    ->label('Nomor Peminjaman')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal_pinjam')
                    ->label('Tanggal Pinjam')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->actions([
                Action::make('kembalikan')
                    ->label('Kembalikan Alat')
                    ->color('info')
                    ->icon('heroicon-o-arrow-left-start-on-rectangle')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Disetujui)
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pengembalian')
                    ->modalDescription('Petugas akan memverifikasi kondisi barang dan menghitung denda (jika ada). Status akan berubah menjadi "Menunggu Verifikasi Pengembalian".')
                    ->action(function (Peminjaman $record) {
                        $record->update([
                            'status' => PeminjamanStatus::Menunggu_Verifikasi_Kembali,
                        ]);

                        Notification::make()->title('Pengajuan pengembalian berhasil! Menunggu verifikasi petugas.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPeminjaman::route('/'),
            'create' => CreatePeminjaman::route('/create'),
        ];
    }
}