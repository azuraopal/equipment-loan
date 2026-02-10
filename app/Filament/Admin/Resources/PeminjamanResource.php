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
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use BackedEnum;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PeminjamanResource extends Resource
{
    protected static ?string $model = Peminjaman::class;
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?string $navigationLabel = 'Peminjaman';
    protected static ?string $slug = 'peminjaman';
    protected static ?string $modelLabel = 'Peminjaman';
    protected static ?string $pluralModelLabel = 'Peminjaman';
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
        return $schema
            ->components([
                Section::make('Informasi Peminjaman')
                    ->description('Detail peminjam & waktu.')
                    ->icon('heroicon-m-information-circle')
                    ->schema([
                        Select::make('user_id')
                            ->label('Peminjam')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon('heroicon-m-user')
                            ->columnSpanFull(),

                        DatePicker::make('tanggal_pinjam')
                            ->label('Tanggal Pinjam')
                            ->default(now())
                            ->required()
                            ->native(false)
                            ->prefixIcon('heroicon-m-calendar'),

                        DatePicker::make('tanggal_kembali_rencana')
                            ->label('Rencana Kembali')
                            ->required()
                            ->native(false)
                            ->after('tanggal_pinjam')
                            ->prefixIcon('heroicon-m-calendar-days'),

                        Textarea::make('keperluan')
                            ->label('Keperluan Peminjaman')
                            ->required()
                            ->rows(4)
                            ->placeholder('Jelaskan keperluan peminjaman secara singkat...')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Daftar Barang')
                    ->description('Item yang akan dipinjam oleh user.')
                    ->icon('heroicon-m-shopping-bag')
                    ->schema([
                        Repeater::make('peminjamanDetails')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->label('Nama Alat')
                                    ->options(Alat::where('stok', '>', 0)->pluck('nama_alat', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->live()
                                    ->prefixIcon('heroicon-m-wrench')
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $stok = Alat::find($state)?->stok ?? 0;
                                        $set('max_stok', $stok);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('jumlah')
                                    ->label('Qty')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->maxValue(fn(Get $get) => $get('max_stok') ?? 100)
                                    ->required()
                                    ->prefixIcon('heroicon-m-hashtag')
                                    ->columnSpan(1),
                                Hidden::make('max_stok'),
                            ])
                            ->minItems(1)
                            ->columns(3)
                            ->itemLabel(fn(array $state): ?string => Alat::find($state['alat_id'] ?? null)?->nama_alat ?? null)
                            ->collapsible()
                            ->cloneable()
                            ->addActionLabel('Tambah Item Lain'),
                    ]),
            ]);
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
                    ->modalHeading('Setujui Peminjaman')
                    ->modalContent(function (Peminjaman $record) {
                        $record->load('peminjamanDetails.alat', 'user');
                        $html = '<div style="font-size:14px; line-height:1.7;">';
                        $html .= '<div style="display:flex; gap:24px; margin-bottom:12px;">';
                        $html .= '<div><span style="color:#9ca3af;">Peminjam</span><br><strong>' . e($record->user->name) . '</strong></div>';
                        $html .= '<div><span style="color:#9ca3af;">No. Peminjaman</span><br><strong>' . e($record->nomor_peminjaman) . '</strong></div>';
                        $html .= '<div><span style="color:#9ca3af;">Periode</span><br><strong>' . Carbon::parse($record->tanggal_pinjam)->format('d/m/Y') . ' — ' . Carbon::parse($record->tanggal_kembali_rencana)->format('d/m/Y') . '</strong></div>';
                        $html .= '</div>';
                        $html .= '<div style="border-top:1px solid rgba(255,255,255,0.1); padding-top:12px; margin-top:4px;">';
                        $html .= '<span style="color:#9ca3af; font-size:12px; text-transform:uppercase; letter-spacing:0.05em;">Barang yang dipinjam</span>';
                        $html .= '<table style="width:100%; margin-top:8px; border-collapse:collapse;">';
                        $html .= '<thead><tr style="border-bottom:1px solid rgba(255,255,255,0.1); text-align:left;">';
                        $html .= '<th style="padding:6px 8px; color:#9ca3af; font-weight:500; font-size:12px;">Nama Alat</th>';
                        $html .= '<th style="padding:6px 8px; color:#9ca3af; font-weight:500; font-size:12px; text-align:center;">Qty</th>';
                        $html .= '<th style="padding:6px 8px; color:#9ca3af; font-weight:500; font-size:12px; text-align:center;">Stok Sisa</th>';
                        $html .= '</tr></thead><tbody>';
                        foreach ($record->peminjamanDetails as $detail) {
                            $alat = $detail->alat;
                            $stokColor = $alat->stok < $detail->jumlah ? '#ef4444' : '#22c55e';
                            $html .= '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">';
                            $html .= '<td style="padding:8px;">' . e($alat->nama_alat) . '</td>';
                            $html .= '<td style="padding:8px; text-align:center; font-weight:600;">' . $detail->jumlah . '</td>';
                            $html .= '<td style="padding:8px; text-align:center; color:' . $stokColor . '; font-weight:600;">' . $alat->stok . '</td>';
                            $html .= '</tr>';
                        }
                        $html .= '</tbody></table></div></div>';
                        return new HtmlString($html);
                    })
                    ->modalIcon('heroicon-o-check-circle')
                    ->modalSubmitActionLabel('Ya, Setujui')
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
                    ->visible(fn(Peminjaman $r) => in_array($r->status, [PeminjamanStatus::Disetujui, PeminjamanStatus::Menunggu_Verifikasi_Kembali]))
                    ->modalHeading('Verifikasi Pengembalian Barang')
                    ->modalDescription('Periksa kondisi fisik setiap barang yang dikembalikan. Denda akan dihitung secara otomatis berdasarkan keterlambatan dan kondisi barang.')
                    ->modalWidth('4xl')
                    ->modalIcon('heroicon-o-arrow-path-rounded-square')
                    ->modalSubmitActionLabel('Konfirmasi Pengembalian')
                    ->form(fn(Schema $schema) => $schema->components([

                        Section::make('Tanggal Pengembalian')
                            ->description('Pilih tanggal barang dikembalikan. Keterlambatan & denda dihitung otomatis.')
                            ->icon('heroicon-o-calendar-days')
                            ->columns(3)
                            ->schema([
                                DatePicker::make('tanggal_kembali_real')
                                    ->label('Tanggal Dikembalikan')
                                    ->native(false)
                                    ->default(now())
                                    ->required()
                                    ->live()
                                    ->helperText('Ubah tanggal untuk menghitung ulang denda.')
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

                                TextInput::make('hari_terlambat')
                                    ->label('Hari Terlambat')
                                    ->numeric()
                                    ->default(fn(Peminjaman $record) => DendaService::hitungHariTerlambat($record, Carbon::parse(now())))
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('hari')
                                    ->helperText('Dihitung otomatis'),

                                TextInput::make('denda_keterlambatan')
                                    ->label('Denda Keterlambatan')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(fn(Peminjaman $record) => DendaService::hitungDendaTelat($record, Carbon::parse(now())))
                                    ->disabled()
                                    ->dehydrated()
                                    ->helperText('Tarif: Rp5.000/hari'),
                            ]),

                        Section::make('Kondisi Barang')
                            ->description('Periksa dan tentukan kondisi setiap barang yang dikembalikan.')
                            ->icon('heroicon-o-cube')
                            ->schema([
                                Repeater::make('items')
                                    ->hiddenLabel()
                                    ->live()
                                    ->schema([
                                        Hidden::make('alat_id'),
                                        Hidden::make('jumlah'),
                                        Hidden::make('harga_satuan'),

                                        TextInput::make('nama_alat')
                                            ->label('Nama Alat')
                                            ->disabled()
                                            ->dehydrated()
                                            ->prefixIcon('heroicon-m-wrench-screwdriver'),

                                        TextInput::make('jumlah_display')
                                            ->label('Jumlah')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefixIcon('heroicon-m-hashtag'),

                                        Select::make('kondisi_kembali')
                                            ->label('Kondisi')
                                            ->native(false)
                                            ->options([
                                                'Baik' => '✅ Baik (Denda 0)',
                                                'Rusak' => '⚠️ Rusak (50% harga)',
                                                'Hilang' => '❌ Hilang (100% + Admin Rp25rb)',
                                            ])
                                            ->required()
                                            ->default('Baik')
                                            ->reactive()
                                            ->prefixIcon('heroicon-m-shield-check')
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
                                            ->disabled()
                                            ->dehydrated(),

                                        Textarea::make('catatan_kondisi')
                                            ->label('Catatan Kerusakan/Kehilangan')
                                            ->placeholder('Jelaskan detail kerusakan atau kehilangan barang...')
                                            ->visible(fn(Get $get) => in_array($get('kondisi_kembali'), ['Rusak', 'Hilang']))
                                            ->columnSpanFull()
                                            ->rows(2),
                                    ])
                                    ->columns(4)
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
                            ]),

                        Section::make('Ringkasan Denda & Pembayaran')
                            ->description('Total denda dihitung otomatis dari keterlambatan + kondisi barang.')
                            ->icon('heroicon-o-banknotes')
                            ->columns(2)
                            ->schema([
                                TextInput::make('total_denda')
                                    ->label('Total Denda Keseluruhan')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->default(fn(Peminjaman $record) => DendaService::hitungDendaTelat($record, Carbon::parse(now())))
                                    ->disabled()
                                    ->dehydrated()
                                    ->hint('Dihitung otomatis')
                                    ->hintIcon('heroicon-m-calculator')
                                    ->hintColor('success')
                                    ->extraInputAttributes(['style' => 'font-weight: 700; font-size: 1.15em; color: #065f46;']),

                                Select::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->options([
                                        'Belum_Lunas' => '🔴 Belum Lunas',
                                        'Lunas' => '🟢 Lunas',
                                    ])
                                    ->default('Belum_Lunas')
                                    ->prefixIcon('heroicon-m-credit-card'),
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
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('4xl')
                    ->modalContent(fn(Peminjaman $record) => self::renderViewContent($record))
                    ->modalHeading('Detail Peminjaman'),
                EditAction::make(),
                DeleteAction::make(),
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

    protected static function renderViewContent(Peminjaman $record): HtmlString
    {
        $record->load('peminjamanDetails.alat', 'user', 'approvedBy');
        $html = '<div style="font-size:14px; line-height:1.7;">';
        $html .= '<div style="background-color:rgba(255,255,255,0.03); padding:16px; border-radius:8px; display:flex; gap:24px; flex-wrap:wrap; margin-bottom:20px;">';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">No. Peminjaman</span><br><strong style="font-size:16px;">' . e($record->nomor_peminjaman) . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Peminjam</span><br><strong>' . e($record->user->name) . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Status</span><br>' . $record->status->name . '</div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Tanggal Pinjam</span><br><strong>' . Carbon::parse($record->tanggal_pinjam)->format('d M Y') . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Rencana Kembali</span><br><strong>' . Carbon::parse($record->tanggal_kembali_rencana)->format('d M Y') . '</strong></div>';
        if ($record->approvedBy) {
            $html .= '<div><span style="color:#9ca3af; font-size:12px;">Disetujui Oleh</span><br><strong>' . e($record->approvedBy->name) . '</strong></div>';
        }
        $html .= '</div>';

        $html .= '<div style="margin-bottom:20px;">';
        $html .= '<span style="color:#9ca3af; font-size:12px; text-transform:uppercase; letter-spacing:0.05em; font-weight:600;">Keperluan</span>';
        $html .= '<p style="margin-top:4px; font-style:italic;">' . nl2br(e($record->keperluan ?? '-')) . '</p>';
        $html .= '</div>';

        $html .= '<div style="border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden;">';
        $html .= '<div style="background-color:rgba(255,255,255,0.05); padding:10px 16px; font-weight:600; font-size:13px; color:#d1d5db;">Barang yang Dipinjam</div>';
        $html .= '<table style="width:100%; border-collapse:collapse;">';
        $html .= '<thead><tr style="border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; background-color:rgba(0,0,0,0.2);">';
        $html .= '<th style="padding:10px 16px; color:#9ca3af; font-weight:500; font-size:12px;">Alat</th>';
        $html .= '<th style="padding:10px 16px; color:#9ca3af; font-weight:500; font-size:12px; text-align:right;">Jumlah</th>';
        $html .= '</tr></thead><tbody>';
        foreach ($record->peminjamanDetails as $detail) {
            $html .= '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">';
            $html .= '<td style="padding:12px 16px;">';
            $html .= '<div style="font-weight:600;">' . e($detail->alat->nama_alat) . '</div>';
            $html .= '<div style="font-size:11px; color:#6b7280;">' . e($detail->alat->merk ?? '') . '</div>';
            $html .= '</td>';
            $html .= '<td style="padding:12px 16px; text-align:right; font-weight:600;">' . $detail->jumlah . ' Unit</td>';
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';
        return new HtmlString($html);
    }
}