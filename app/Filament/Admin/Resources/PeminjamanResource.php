<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Peminjaman\Pages\CreatePeminjaman;
use App\Filament\Admin\Resources\Peminjaman\Pages\EditPeminjaman;
use App\Filament\Admin\Resources\Peminjaman\Pages\ListPeminjaman;
use App\Models\Alat;
use App\Models\Peminjaman;
use App\Enums\PeminjamanStatus;
use App\Models\Pengembalian;
use App\Models\PengembalianDetail;
use App\Services\DendaService;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                    ->modalDescription('Cek kondisi fisik barang. Denda dihitung otomatis.')
                    ->modalWidth('3xl')
                    ->form(fn(Schema $schema) => $schema->components([

                        DatePicker::make('tanggal_kembali_real')
                            ->label('Tanggal Dikembalikan')
                            ->default(now())
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, Peminjaman $record) {
                                $tanggal = $get('tanggal_kembali_real');
                                if ($tanggal) {
                                    $hari = DendaService::hitungHariTerlambat($record, Carbon::parse($tanggal));
                                    $denda = DendaService::hitungDendaTelat($record, Carbon::parse($tanggal));
                                    $set('hari_terlambat', $hari);
                                    $set('denda_keterlambatan', $denda);
                                    self::hitungGrandTotal($set, $get);
                                }
                            }),

                        Grid::make(2)->schema([
                            TextInput::make('hari_terlambat')
                                ->label('Hari Terlambat')
                                ->numeric()
                                ->default(fn(Peminjaman $record) => DendaService::hitungHariTerlambat($record, Carbon::parse(now())))
                                ->readOnly()
                                ->extraInputAttributes(['style' => 'background-color: #f3f4f6;']),

                            TextInput::make('denda_keterlambatan')
                                ->label('Denda Telat (Rp5.000/hari)')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(fn(Peminjaman $record) => DendaService::hitungDendaTelat($record, Carbon::parse(now())))
                                ->readOnly()
                                ->extraInputAttributes(['style' => 'background-color: #f3f4f6;']),
                        ]),

                        Repeater::make('items')
                            ->label('Kondisi Barang')
                            ->live()
                            ->schema([
                                Hidden::make('alat_id'),
                                Hidden::make('jumlah'),
                                Hidden::make('harga_satuan'),

                                TextInput::make('nama_alat')
                                    ->label('Alat')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('jumlah_display')
                                    ->label('Jumlah')
                                    ->disabled()
                                    ->dehydrated(false),

                                Select::make('kondisi_kembali')
                                    ->label('Kondisi')
                                    ->options([
                                        'Baik' => 'Baik (Denda 0)',
                                        'Rusak' => 'Rusak (50% harga)',
                                        'Hilang' => 'Hilang (100% + Admin Rp25rb)',
                                    ])
                                    ->required()
                                    ->default('Baik')
                                    ->reactive()
                                    ->afterStateUpdated(function (Set $set, Get $get) {
                                        $harga = (float) $get('harga_satuan');
                                        $jumlah = (int) $get('jumlah');
                                        $kondisi = $get('kondisi_kembali');
                                        $denda = DendaService::hitungDendaItem($kondisi, $harga, $jumlah);
                                        $set('denda_item', $denda);
                                    }),

                                TextInput::make('denda_item')
                                    ->label('Denda Item')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(0)
                                    ->readOnly()
                                    ->dehydrated()
                                    ->extraInputAttributes(['style' => 'background-color: #f3f4f6;']),

                                Textarea::make('catatan_kondisi')
                                    ->label('Catatan')
                                    ->placeholder('Keterangan kerusakan/kehilangan...')
                                    ->visible(fn(Get $get) => in_array($get('kondisi_kembali'), ['Rusak', 'Hilang']))
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->default(fn(Peminjaman $record) => $record->peminjamanDetails->map(fn($d) => [
                                'alat_id' => $d->alat_id,
                                'jumlah' => $d->jumlah,
                                'harga_satuan' => $d->alat->harga_satuan,
                                'nama_alat' => $d->alat->nama_alat,
                                'jumlah_display' => $d->jumlah . ' unit',
                                'kondisi_kembali' => 'Baik',
                                'denda_item' => 0,
                                'catatan_kondisi' => null,
                            ])->toArray())
                            ->afterStateUpdated(fn(Set $set, Get $get) => self::hitungGrandTotal($set, $get)),

                        Grid::make(2)->schema([
                            TextInput::make('total_denda')
                                ->label('Total Denda')
                                ->prefix('Rp')
                                ->numeric()
                                ->default(fn(Peminjaman $record) => DendaService::hitungDendaTelat($record, Carbon::parse(now())))
                                ->readOnly()
                                ->extraInputAttributes(['style' => 'font-weight: bold; font-size: 1.1em; background-color: #ecfdf5; color: #065f46;']),

                            Select::make('status_pembayaran')
                                ->label('Status Bayar')
                                ->options([
                                    'Belum_Lunas' => 'Belum Lunas',
                                    'Lunas' => 'Lunas',
                                ])
                                ->default('Belum_Lunas'),
                        ]),
                    ]))
                    ->action(function (Peminjaman $record, array $data) {
                        DB::transaction(function () use ($record, $data) {

                            $dendaTelat = (float) ($data['denda_keterlambatan'] ?? 0);
                            $totalDendaItem = 0;
                            $dendaKerusakan = 0;
                            $dendaKehilangan = 0;

                            foreach ($record->peminjamanDetails as $detail) {
                                $detail->alat->increment('stok', $detail->jumlah);
                            }

                            $items = $data['items'] ?? [];
                            foreach ($items as $item) {
                                $kondisi = $item['kondisi_kembali'] ?? 'Baik';
                                $dendaItem = (float) ($item['denda_item'] ?? 0);
                                $totalDendaItem += $dendaItem;

                                if ($kondisi === 'Rusak') {
                                    $dendaKerusakan += $dendaItem;
                                } elseif ($kondisi === 'Hilang') {
                                    $dendaKehilangan += $dendaItem;
                                }
                            }

                            $totalDenda = $dendaTelat + $totalDendaItem;
                            $statusBayar = $data['status_pembayaran'] ?? ($totalDenda > 0 ? 'Belum_Lunas' : 'Lunas');

                            $pengembalian = Pengembalian::create([
                                'peminjaman_id' => $record->id,
                                'petugas_id' => auth()->id(),
                                'nomor_pengembalian' => 'RET-' . time(),
                                'tanggal_kembali_real' => $data['tanggal_kembali_real'],
                                'hari_terlambat' => (int) ($data['hari_terlambat'] ?? 0),
                                'denda_keterlambatan' => $dendaTelat,
                                'denda_kerusakan' => $dendaKerusakan,
                                'denda_kehilangan' => $dendaKehilangan,
                                'total_denda' => $totalDenda,
                                'status_pembayaran' => $statusBayar,
                                'catatan_pengembalian' => null,
                            ]);

                            foreach ($items as $item) {
                                PengembalianDetail::create([
                                    'pengembalian_id' => $pengembalian->id,
                                    'alat_id' => $item['alat_id'],
                                    'jumlah_kembali' => $item['jumlah'],
                                    'kondisi_kembali' => $item['kondisi_kembali'] ?? 'Baik',
                                    'denda_item' => $item['denda_item'] ?? 0,
                                    'catatan_kondisi' => $item['catatan_kondisi'] ?? null,
                                ]);
                            }

                            $record->update([
                                'status' => PeminjamanStatus::Kembali,
                                'tanggal_kembali_real' => $data['tanggal_kembali_real'],
                            ]);
                        });

                        $totalDenda = (float) ($data['total_denda'] ?? 0);
                        Notification::make()
                            ->title('Barang Diterima & Stok Kembali')
                            ->body($totalDenda > 0
                                ? 'Total denda: Rp' . number_format($totalDenda, 0, ',', '.')
                                : 'Pengembalian sukses, tidak ada denda.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function hitungGrandTotal(Set $set, Get $get): void
    {
        $items = $get('items');
        $totalDendaItem = 0;
        if (is_array($items)) {
            foreach ($items as $item) {
                $totalDendaItem += (float) ($item['denda_item'] ?? 0);
            }
        }
        $dendaTelat = (float) ($get('denda_keterlambatan') ?? 0);
        $set('total_denda', $totalDendaItem + $dendaTelat);
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

