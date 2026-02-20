<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Pengembalian\Pages\CreatePengembalian;
use App\Filament\Admin\Resources\Pengembalian\Pages\EditPengembalian;
use App\Filament\Admin\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Pengembalian;
use App\Services\PaymentService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use Illuminate\Support\HtmlString;
use UnitEnum;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path-rounded-square';

    protected static ?string $navigationLabel = 'Pengembalian';
    protected static string|UnitEnum|null $navigationGroup = 'Transaksi';
    protected static ?string $pluralModelLabel = 'Pengembalian';

    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Data Utama')
                    ->schema([
                        Select::make('peminjaman_id')
                            ->relationship('peminjaman', 'nomor_peminjaman')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live(),
                        TextInput::make('nomor_pengembalian')
                            ->required()
                            ->unique(ignoreRecord: true),
                        DatePicker::make('tanggal_kembali_real')
                            ->native(false)
                            ->required(),
                        Select::make('petugas_id')
                            ->relationship('petugas', 'name')
                            ->required(),
                    ])->columns(2),

                Section::make('Detail Barang & Kondisi')
                    ->schema([
                        Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Select::make('alat_id')
                                    ->relationship('alat', 'nama_alat')
                                    ->required()
                                    ->searchable()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(), // Mencegah duplikasi alat
                                TextInput::make('jumlah_kembali')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),
                                Select::make('kondisi_kembali')
                                    ->options([
                                        'Baik' => 'Baik',
                                        'Rusak' => 'Rusak',
                                        'Hilang' => 'Hilang',
                                    ])
                                    ->required(),
                                TextInput::make('denda_item')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0),
                                Textarea::make('catatan_kondisi')
                                    ->columnSpanFull(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Alat'),
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nomor_pengembalian')->searchable()->sortable(),
                TextColumn::make('peminjaman.nomor_peminjaman')->label('No. Pinjam')->searchable(),
                TextColumn::make('peminjaman.user.name')->label('Peminjam')->searchable(),
                TextColumn::make('tanggal_kembali_real')->date()->label('Tgl Kembali'),
                TextColumn::make('total_denda')->money('IDR'),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->formatStateUsing(fn(string $state) => str_replace('_', ' ', $state))
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Action::make('konfirmasi_cash')
                    ->label('Konfirmasi Cash')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Konfirmasi Pembayaran Cash')
                    ->modalDescription(fn(Pengembalian $record) => 'Konfirmasi pembayaran cash sebesar Rp ' . number_format((float) $record->total_denda, 0, ',', '.') . ' dari ' . ($record->peminjaman->user->name ?? 'peminjam') . '?')
                    ->modalSubmitActionLabel('Ya, Konfirmasi Lunas')
                    ->visible(fn(Pengembalian $record) => $record->payments()
                        ->where('payment_type', 'cash')
                        ->where('status', 'pending_verification')
                        ->exists())
                    ->action(function (Pengembalian $record) {
                        $payment = $record->payments()
                            ->where('payment_type', 'cash')
                            ->where('status', 'pending_verification')
                            ->latest()
                            ->first();

                        if ($payment) {
                            app(PaymentService::class)->confirmCashPayment($payment);

                            Notification::make()
                                ->title('Pembayaran cash dikonfirmasi!')
                                ->body('Status pembayaran telah diubah menjadi Lunas.')
                                ->success()
                                ->send();
                        }
                    }),
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalWidth('4xl')
                    ->modalContent(fn(Pengembalian $record) => self::renderViewContent($record))
                    ->modalHeading('Detail Pengembalian'),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    protected static function renderViewContent(Pengembalian $record): HtmlString
    {
        $record->load('details.alat', 'peminjaman.user', 'petugas');

        $html = '<div style="font-size:14px; line-height:1.7;">';

        $html .= '<div style="background-color:rgba(255,255,255,0.03); padding:16px; border-radius:8px; display:flex; gap:24px; flex-wrap:wrap; margin-bottom:20px;">';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">No. Pengembalian</span><br><strong style="font-size:16px;">' . e($record->nomor_pengembalian) . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Peminjam</span><br><strong>' . e($record->peminjaman->user->name ?? '-') . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Tgl Kembali</span><br><strong>' . Carbon::parse($record->tanggal_kembali_real)->format('d M Y') . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Petugas</span><br><strong>' . e($record->petugas->name ?? '-') . '</strong></div>';
        $html .= '<div><span style="color:#9ca3af; font-size:12px;">Status Pembayaran</span><br>';
        $statusColor = $record->status_pembayaran === 'Lunas' ? '#22c55e' : '#ef4444';
        $html .= '<span style="color:' . $statusColor . '; font-weight:bold;">' . str_replace('_', ' ', $record->status_pembayaran) . '</span></div>';
        $html .= '</div>';

        $html .= '<div style="display:flex; gap:20px; margin-bottom:20px;">';
        $html .= '<div style="flex:1; background-color:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); padding:12px; border-radius:8px;">';
        $html .= '<span style="color:#fca5a5; font-size:12px;">Denda Keterlambatan</span><br>';
        $html .= '<strong style="color:#fecaca; font-size:16px;">Rp ' . number_format((float) $record->denda_keterlambatan, 0, ',', '.') . '</strong>';
        $html .= '</div>';
        $html .= '<div style="flex:1; background-color:rgba(239,68,68,0.1); border:1px solid rgba(239,68,68,0.2); padding:12px; border-radius:8px;">';
        $html .= '<span style="color:#fca5a5; font-size:12px;">Total Denda</span><br>';
        $html .= '<strong style="color:#fecaca; font-size:16px;">Rp ' . number_format((float) $record->total_denda, 0, ',', '.') . '</strong>';
        $html .= '</div>';
        if ($record->hari_terlambat > 0) {
            $html .= '<div style="flex:1; background-color:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); padding:12px; border-radius:8px;">';
            $html .= '<span style="color:#9ca3af; font-size:12px;">Terlambat</span><br>';
            $html .= '<strong>' . $record->hari_terlambat . ' Hari</strong>';
            $html .= '</div>';
        }
        $html .= '</div>';

        $html .= '<div style="border:1px solid rgba(255,255,255,0.1); border-radius:8px; overflow:hidden;">';
        $html .= '<div style="background-color:rgba(255,255,255,0.05); padding:10px 16px; font-weight:600; font-size:13px; color:#d1d5db;">Detail Barang Dikembalikan</div>';
        $html .= '<table style="width:100%; border-collapse:collapse;">';
        $html .= '<thead><tr style="border-bottom:1px solid rgba(255,255,255,0.05); text-align:left; background-color:rgba(0,0,0,0.2);">';
        $html .= '<th style="padding:10px 16px; color:#9ca3af; font-weight:500; font-size:12px;">Alat</th>';
        $html .= '<th style="padding:10px 16px; color:#9ca3af; font-weight:500; font-size:12px; text-align:center;">Kondisi</th>';
        $html .= '<th style="padding:10px 16px; color:#9ca3af; font-weight:500; font-size:12px; text-align:right;">Denda Item</th>';
        $html .= '</tr></thead><tbody>';

        foreach ($record->details as $detail) {
            $html .= '<tr style="border-bottom:1px solid rgba(255,255,255,0.05);">';

            $html .= '<td style="padding:12px 16px;">';
            $html .= '<div style="font-weight:600;">' . e($detail->alat->nama_alat) . '</div>';
            $html .= '<div style="font-size:11px; color:#6b7280;">' . $detail->jumlah_kembali . ' Unit</div>';
            if ($detail->catatan_kondisi) {
                $html .= '<div style="font-size:11px; color:#f87171; margin-top:2px;">Note: ' . e($detail->catatan_kondisi) . '</div>';
            }
            $html .= '</td>';

            $kondisiColor = match ($detail->kondisi_kembali) {
                'Baik' => '#22c55e',
                'Rusak' => '#eab308',
                'Hilang' => '#ef4444',
                default => '#9ca3af'
            };
            $html .= '<td style="padding:12px 16px; text-align:center;">';
            $html .= '<span style="color:' . $kondisiColor . '; font-weight:600; font-size:12px; border:1px solid ' . $kondisiColor . '; padding:2px 8px; border-radius:12px;">' . $detail->kondisi_kembali . '</span>';
            $html .= '</td>';

            $html .= '<td style="padding:12px 16px; text-align:right; font-family:monospace;">';
            if ($detail->denda_item > 0) {
                $html .= '<span style="color:#ef4444;">Rp ' . number_format((float) $detail->denda_item, 0, ',', '.') . '</span>';
            } else {
                $html .= '<span style="color:#9ca3af;">-</span>';
            }
            $html .= '</td>';

            $html .= '</tr>';
        }
        $html .= '</tbody></table></div></div>';

        return new HtmlString($html);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'create' => CreatePengembalian::route('/create'),
            'edit' => EditPengembalian::route('/{record}/edit'),
        ];
    }
}