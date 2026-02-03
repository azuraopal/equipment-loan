<?php

namespace App\Filament\Petugas\Resources\Peminjaman;

use App\Filament\Petugas\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Models\Peminjaman;
use App\Enums\PeminjamanStatus;
use App\Models\Pengembalian;
use Exception;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action; 
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use BackedEnum;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_peminjaman')->searchable(),
                TextColumn::make('user.name')->label('Peminjam'),
                TextColumn::make('tanggal_pinjam')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->color('success')
                    ->icon('heroicon-o-check')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Menunggu)
                    ->requiresConfirmation()
                    ->action(function (Peminjaman $record) {
                        DB::transaction(function () use ($record) {
                            foreach ($record->peminjamanDetails as $detail) {
                                $alat = $detail->alat;
                                if ($alat->stok < $detail->jumlah) {
                                    Notification::make()->title("Stok {$alat->nama_alat} Kurang!")->danger()->send();
                                    throw new Exception('Stok Habis');
                                }
                                $alat->decrement('stok', $detail->jumlah);
                            }
                            $record->update(['status' => PeminjamanStatus::Disetujui]);
                        });
                        Notification::make()->title('Berhasil Disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Menunggu)
                    ->action(fn(Peminjaman $r) => $r->update(['status' => PeminjamanStatus::Ditolak])),

                Action::make('return')
                    ->label('Proses Kembali')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Disetujui)
                    ->form(fn(Schema $schema) => $schema->components([
                        DatePicker::make('tanggal_kembali_real')
                            ->default(now())
                            ->required(),
                        TextInput::make('denda')
                            ->label('Total Denda (Terlambat/Rusak)')
                            ->numeric()
                            ->prefix('Rp')
                            ->default(0),
                    ]))
                    ->action(function (Peminjaman $record, array $data) {
                        DB::transaction(function () use ($record, $data) {
                            foreach ($record->peminjamanDetails as $detail) {
                                $detail->alat->increment('stok', $detail->jumlah);
                            }

                            Pengembalian::create([
                                'peminjaman_id' => $record->id,
                                'petugas_id' => auth()->id(),
                                'tanggal_kembali_real' => $data['tanggal_kembali_real'],
                                'denda' => $data['denda'],
                            ]);

                            $record->update(['status' => PeminjamanStatus::Kembali]);
                        });
                        Notification::make()->title('Pengembalian Selesai')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPeminjaman::route('/')];
    }
}