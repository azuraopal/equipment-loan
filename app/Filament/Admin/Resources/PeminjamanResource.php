<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Peminjaman\Pages\CreatePeminjaman;
use App\Filament\Admin\Resources\Peminjaman\Pages\EditPeminjaman;
use App\Filament\Admin\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Models\Peminjaman;
use App\Enums\PeminjamanStatus;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use BackedEnum;
use UnitEnum;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Verifikasi Peminjaman';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return true;
    }
    public static function canDelete($record): bool
    {
        return true;
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
                TextColumn::make('user.name')->label('Peminjam')->searchable(),
                TextColumn::make('tanggal_pinjam')->date(),
                TextColumn::make('status')->badge(),
            ])
            ->defaultSort('created_at', 'desc')
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
                                    Notification::make()
                                        ->title("Gagal: Stok {$alat->nama_alat} tidak cukup!")
                                        ->danger()
                                        ->send();
                                    throw new Exception('Stok Habis');
                                }
                                $alat->decrement('stok', $detail->jumlah);
                            }
                            $record->update([
                                'status' => PeminjamanStatus::Disetujui,
                                'approved_by' => auth()->id(),
                                'approved_at' => now(),
                            ]);
                        });
                        Notification::make()->title('Peminjaman Disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->color('danger')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Menunggu)
                    ->action(function (Peminjaman $r) {
                        $r->update([
                            'status' => PeminjamanStatus::Ditolak,
                            'rejected_by' => auth()->id(),
                            'rejected_at' => now(),
                        ]);
                    }),

                Action::make('return')
                    ->label('Terima Barang')
                    ->color('info')
                    ->icon('heroicon-o-arrow-path')
                    ->visible(fn(Peminjaman $r) => $r->status === PeminjamanStatus::Disetujui)
                    ->modalHeading('Verifikasi Pengembalian Barang')
                    ->modalDescription('Cek kondisi fisik barang. Isi denda jika ada kerusakan/kehilangan.')
                    ->form(fn(Schema $schema) => $schema->components([

                        DatePicker::make('tanggal_kembali_real')
                            ->label('Tanggal Dikembalikan')
                            ->default(now())
                            ->required(),

                        Textarea::make('info_barang')
                            ->label('Barang yang harus kembali')
                            ->default(
                                fn(Peminjaman $record) =>
                                $record->peminjamanDetails->map(fn($d) => "• {$d->alat->nama_alat} ({$d->jumlah} unit)")->join("\n")
                            )
                            ->disabled()
                            ->rows(3),

                        Grid::make(2)->schema([
                            TextInput::make('denda_keterlambatan')
                                ->label('Denda Telat')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0),

                            TextInput::make('denda_kerusakan')
                                ->label('Denda Rusak/Hilang')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(0)
                                ->live(),
                        ]),

                        Textarea::make('catatan_kondisi')
                            ->label('Keterangan Kerusakan/Kehilangan')
                            ->placeholder('Contoh: Mata bor patah, Kabel putus.')
                            ->visible(fn(Get $get) => (int) $get('denda_kerusakan') > 0)
                            ->required(fn(Get $get) => (int) $get('denda_kerusakan') > 0),
                    ]))
                    ->action(function (Peminjaman $record, array $data) {
                        DB::transaction(function () use ($record, $data) {

                            $totalDenda = $data['denda_keterlambatan'] + $data['denda_kerusakan'];
                            $statusBayar = $totalDenda > 0 ? 'Belum_Lunas' : 'Lunas';

                            foreach ($record->peminjamanDetails as $detail) {
                                $detail->alat->increment('stok', $detail->jumlah);
                            }

                            $pengembalian = Pengembalian::create([
                                'peminjaman_id' => $record->id,
                                'petugas_id' => auth()->id(),
                                'nomor_pengembalian' => 'RET-' . time(),
                                'tanggal_kembali_real' => $data['tanggal_kembali_real'],
                                'denda_keterlambatan' => $data['denda_keterlambatan'],
                                'denda_kerusakan' => $data['denda_kerusakan'],
                                'total_denda' => $totalDenda,
                                'status_pembayaran' => $statusBayar,
                                'catatan_pengembalian' => $data['catatan_kondisi'] ?? null,
                            ]);

                            foreach ($record->peminjamanDetails as $detail) {
                                PengembalianDetail::create([
                                    'pengembalian_id' => $pengembalian->id,
                                    'alat_id' => $detail->alat_id,
                                    'jumlah_kembali' => $detail->jumlah,
                                    'kondisi_kembali' => 'Baik',
                                    'denda_item' => 0,
                                ]);
                            }

                            $record->update([
                                'status' => PeminjamanStatus::Kembali,
                                'tanggal_kembali_real' => $data['tanggal_kembali_real'],
                            ]);
                        });

                        Notification::make()
                            ->title('Barang Diterima & Stok Kembali')
                            ->body($data['denda_kerusakan'] > 0 ? 'Denda kerusakan tercatat.' : 'Pengembalian sukses.')
                            ->success()
                            ->send();
                    }),
            ]);
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