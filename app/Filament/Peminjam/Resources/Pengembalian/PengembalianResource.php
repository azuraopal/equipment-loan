<?php

namespace App\Filament\Peminjam\Resources\Pengembalian;

use App\Filament\Peminjam\Resources\Pengembalian\Pages\CreatePengembalian;
use App\Filament\Peminjam\Resources\Pengembalian\Pages\ListPengembalian;
use App\Models\Payment;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use Auth;
use Carbon\Carbon;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Midtrans\Config;
use Midtrans\Transaction;

class PengembalianResource extends Resource
{
    protected static ?string $model = Pengembalian::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box-arrow-down';

    protected static ?string $navigationLabel = 'Kembalikan Alat';
    protected static ?string $pluralModelLabel = 'Kembalikan Alat';

    protected static ?string $slug = 'pengembalian';

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
                Select::make('peminjaman_id')
                    ->label('Pilih Peminjaman')
                    ->options(function () {

                        return Peminjaman::where('user_id', Auth::id())
                            ->where('status', 'Disetujui')
                            ->whereDoesntHave('pengembalian')
                            ->pluck('nomor_peminjaman', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->preload(),

                DatePicker::make('tanggal_kembali_real')
                    ->label('Tanggal Pengembalian')
                    ->default(now())
                    ->required()
                    ->native(false)
                    ->maxDate(now()),

                Hidden::make('nomor_pengembalian')
                    ->default(fn() => 'KEM-' . strtoupper(uniqid())),

            ]);
    }

    private static function formatPaymentType(?string $type): string
    {
        if (!$type) {
            return 'Tidak diketahui';
        }

        return match ($type) {
            'qris' => 'QRIS',
            'bank_transfer' => 'Transfer Bank',
            'credit_card' => 'Kartu Kredit',
            'cstore' => 'Convenience Store',
            'echannel' => 'Mandiri Bill',
            'gopay' => 'GoPay',
            'shopeepay' => 'ShopeePay',
            default => strtoupper(str_replace('_', ' ', $type)),
        };
    }

    private static function getPaymentWithDetails(Pengembalian $record): ?Payment
    {
        /** @var Payment|null $payment */
        $payment = $record->payments()->where('status', 'success')->latest()->first()
            ?? $record->payments()->latest()->first();

        if (!$payment) {
            return null;
        }

        if ($payment->order_id && !$payment->payment_type) {
            try {
                Config::$serverKey = config('services.midtrans.server_key');
                Config::$isProduction = config('services.midtrans.is_production');
                Config::$isSanitized = config('services.midtrans.is_sanitized');
                Config::$is3ds = config('services.midtrans.is_3ds');

                /** @var object $status */
                $status = Transaction::status($payment->order_id);

                $payment->update([
                    'payment_type' => $status->payment_type ?? null,
                    'transaction_time' => $status->transaction_time ?? null,
                    'status' => match ($status->transaction_status ?? '') {
                        'settlement', 'capture' => 'success',
                        'expire' => 'expired',
                        'cancel' => 'cancelled',
                        'deny' => 'failed',
                        default => $payment->status,
                    },
                ]);

                if (in_array($status->transaction_status ?? '', ['settlement', 'capture'])) {
                    $record->update([
                        'status_pembayaran' => 'Lunas',
                        'tanggal_bayar' => $status->transaction_time ?? now(),
                    ]);
                }

                $payment = $payment->fresh();
            } catch (\Exception $e) {
                \Log::debug('Failed to fetch Midtrans status for ' . $payment->order_id . ': ' . $e->getMessage());
            }
        }

        return $payment;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('peminjaman', function ($q) {
                $q->where('user_id', Auth::id());
            }))
            ->columns([
                TextColumn::make('nomor_pengembalian'),
                TextColumn::make('peminjaman.nomor_peminjaman')->label('No. Pinjam'),
                TextColumn::make('tanggal_kembali_real')->date(),
                TextColumn::make('status_pembayaran')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        default => 'warning',
                    }),
                TextColumn::make('total_denda')->money('IDR'),
            ])
            ->actions([
                ViewAction::make()
                    ->modalHeading('Detail Pengembalian')
                    ->modalWidth('3xl')
                    ->infolist([
                        TextEntry::make('nomor_pengembalian')
                            ->label('Nomor Pengembalian')
                            ->icon('heroicon-o-document-text')
                            ->weight('bold')
                            ->columnSpanFull(),

                        TextEntry::make('peminjaman.nomor_peminjaman')
                            ->label('Nomor Peminjaman')
                            ->icon('heroicon-o-clipboard-document'),

                        TextEntry::make('tanggal_kembali_real')
                            ->label('Tanggal Pengembalian')
                            ->date('d F Y')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('hari_terlambat')
                            ->label('Hari Terlambat')
                            ->suffix(' hari')
                            ->icon('heroicon-o-clock')
                            ->color(fn($state) => $state > 0 ? 'danger' : 'success'),

                        TextEntry::make('catatan_pengembalian')
                            ->label('Catatan')
                            ->icon('heroicon-o-chat-bubble-left-ellipsis')
                            ->placeholder('Tidak ada catatan')
                            ->columnSpanFull(),

                        TextEntry::make('denda_separator')
                            ->label('── Rincian Denda ──')
                            ->default('')
                            ->columnSpanFull(),

                        TextEntry::make('denda_keterlambatan')
                            ->label('Denda Keterlambatan')
                            ->money('IDR')
                            ->icon('heroicon-o-clock'),

                        TextEntry::make('denda_kerusakan')
                            ->label('Denda Kerusakan')
                            ->money('IDR')
                            ->icon('heroicon-o-wrench'),

                        TextEntry::make('denda_kehilangan')
                            ->label('Denda Kehilangan')
                            ->money('IDR')
                            ->icon('heroicon-o-exclamation-triangle'),

                        TextEntry::make('total_denda')
                            ->label('Total Denda')
                            ->money('IDR')
                            ->weight('bold')
                            ->color('danger')
                            ->icon('heroicon-o-banknotes'),

                        TextEntry::make('payment_separator')
                            ->label('── Informasi Pembayaran ──')
                            ->default('')
                            ->columnSpanFull(),

                        TextEntry::make('status_pembayaran')
                            ->label('Status Pembayaran')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'Lunas' => 'success',
                                default => 'warning',
                            }),

                        TextEntry::make('tanggal_bayar_display')
                            ->label('Tanggal Bayar')
                            ->icon('heroicon-o-calendar-days')
                            ->getStateUsing(function (Pengembalian $record): string {
                                if ($record->tanggal_bayar) {
                                    return Carbon::parse($record->tanggal_bayar)->format('d F Y');
                                }
                                $payment = self::getPaymentWithDetails($record);
                                if ($payment && $payment->transaction_time) {
                                    return Carbon::parse($payment->transaction_time)->format('d F Y');
                                }
                                return 'Belum dibayar';
                            }),

                        TextEntry::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->icon('heroicon-o-credit-card')
                            ->getStateUsing(function (Pengembalian $record): string {
                                $payment = self::getPaymentWithDetails($record);
                                return $payment ? self::formatPaymentType($payment->payment_type) : 'Belum ada pembayaran';
                            }),

                        TextEntry::make('payment_time')
                            ->label('Waktu Transaksi')
                            ->icon('heroicon-o-clock')
                            ->getStateUsing(function (Pengembalian $record): string {
                                $payment = self::getPaymentWithDetails($record);
                                if ($payment && $payment->transaction_time) {
                                    return $payment->transaction_time->format('d F Y, H:i:s');
                                }
                                return 'Belum ada transaksi';
                            }),

                        TextEntry::make('payment_order_id')
                            ->label('Order ID')
                            ->icon('heroicon-o-hashtag')
                            ->getStateUsing(function (Pengembalian $record): string {
                                $payment = self::getPaymentWithDetails($record);
                                return $payment ? $payment->order_id : '-';
                            })
                            ->copyable(),

                        RepeatableEntry::make('details')
                            ->label('Detail Barang Dikembalikan')
                            ->columnSpanFull()
                            ->schema([
                                TextEntry::make('alat.nama_alat')
                                    ->label('Nama Alat'),

                                TextEntry::make('jumlah_kembali')
                                    ->label('Jumlah'),

                                TextEntry::make('kondisi_kembali')
                                    ->label('Kondisi')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'Baik' => 'success',
                                        'Rusak' => 'warning',
                                        'Hilang' => 'danger',
                                        default => 'gray',
                                    }),

                                TextEntry::make('catatan_kondisi')
                                    ->label('Catatan')
                                    ->placeholder('-'),

                                TextEntry::make('denda_item')
                                    ->label('Denda')
                                    ->money('IDR'),
                            ]),
                    ]),
                Action::make('bayar_denda')
                    ->label('Bayar Denda')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('danger')
                    ->url(fn(Pengembalian $record) => \App\Filament\Peminjam\Pages\PembayaranDenda::getUrl(['record' => $record]))
                    ->openUrlInNewTab(false)
                    ->visible(fn(Pengembalian $record) => $record->status_pembayaran === 'Belum_Lunas' && $record->total_denda > 0),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPengembalian::route('/'),
            'create' => CreatePengembalian::route('/create'),
        ];
    }
}
