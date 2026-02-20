<?php

namespace App\Filament\Peminjam\Pages;

use App\Models\Pengembalian;
use App\Services\PaymentService;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;

class PembayaranDenda extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected string $view = 'filament.peminjam.pages.pembayaran-denda';

    protected static ?string $slug = 'pembayaran-denda/{record}';

    protected static bool $shouldRegisterNavigation = false;

    public static function getNavigationLabel(): string
    {
        return 'Pembayaran Denda';
    }

    public function getHeading(): string
    {
        return 'Pembayaran Denda';
    }

    public function getSubheading(): ?string
    {
        return 'Selesaikan pembayaran denda pengembalian terlambat';
    }

    public $recordId;
    public $snapToken;
    public ?array $data = [];

    public function getRecordProperty()
    {
        return Pengembalian::with('peminjaman.alats', 'details.alat', 'payments')->find($this->recordId);
    }

    public function mount(Pengembalian $record)
    {
        $this->recordId = $record->id;

        $record = $this->record;

        if ($record->peminjaman->user_id !== Auth::id()) {
            abort(403);
        }

        if ($record->status_pembayaran === 'Lunas') {
            return redirect()->route('filament.peminjam.resources.pengembalian.index');
        }

        $hasPendingCash = $record->payments()
            ->where('payment_type', 'cash')
            ->where('status', 'pending_verification')
            ->exists();

        if (!$hasPendingCash) {
            try {
                $this->snapToken = app(PaymentService::class)->createPayment($record);
            } catch (\Exception $e) {
                $this->snapToken = null;
            }
        }
    }

    public function bayarCashAction(): Action
    {
        return Action::make('bayarCash')
            ->label('Bayar Cash (Tunai)')
            ->icon('heroicon-o-banknotes')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Konfirmasi Pembayaran Cash')
            ->modalDescription('Pembayaran cash akan menunggu verifikasi dari petugas/admin. Pastikan Anda membayar langsung ke petugas.')
            ->modalSubmitActionLabel('Ya, Bayar Cash')
            ->action(function () {
                app(PaymentService::class)->createCashPayment($this->record);

                Notification::make()
                    ->title('Pembayaran cash berhasil diajukan!')
                    ->body('Silakan bayar ke petugas. Status akan diperbarui setelah petugas mengkonfirmasi.')
                    ->success()
                    ->send();

                $this->redirect(static::getUrl(['record' => $this->recordId]));
            });
    }

    public function form(Schema $schema): Schema
    {
        $record = $this->record;
        $hasPendingCash = $record->payments()
            ->where('payment_type', 'cash')
            ->where('status', 'pending_verification')
            ->exists();

        $statusLabel = str_replace('_', ' ', $record->status_pembayaran);
        $isLunas = $record->status_pembayaran === 'Lunas';
        $badgeColor = $isLunas ? 'success' : 'danger';

        return $schema
            ->schema([
                Section::make('Informasi Pengembalian')
                    ->description('Detail pengembalian yang dikenakan denda')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                Placeholder::make('nomor_peminjaman')
                                    ->label('No. Peminjaman')
                                    ->content($record->peminjaman->nomor_peminjaman),

                                Placeholder::make('nomor_pengembalian')
                                    ->label('No. Pengembalian')
                                    ->content($record->nomor_pengembalian),

                                Placeholder::make('tanggal_pinjam')
                                    ->label('Tanggal Pinjam')
                                    ->content($record->peminjaman->tanggal_pinjam?->format('d F Y') ?? $record->peminjaman->tanggal_pinjam),

                                Placeholder::make('tanggal_kembali')
                                    ->label('Tanggal Kembali')
                                    ->content($record->tanggal_kembali_real?->format('d F Y') ?? $record->tanggal_kembali_real),

                                Placeholder::make('hari_terlambat')
                                    ->label('Hari Terlambat')
                                    ->content($record->hari_terlambat . ' hari'),

                                Placeholder::make('status_pembayaran')
                                    ->label('Status Pembayaran')
                                    ->content(new HtmlString(
                                        '<span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom fi-color-' . $badgeColor . ' bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30" style="--c-50:var(--' . $badgeColor . '-50);--c-400:var(--' . $badgeColor . '-400);--c-600:var(--' . $badgeColor . '-600);">' . $statusLabel . '</span>'
                                    )),
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Rincian Denda')
                    ->description('Rincian biaya denda yang harus dibayar')
                    ->icon('heroicon-o-calculator')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                Placeholder::make('denda_keterlambatan')
                                    ->label('Denda Keterlambatan')
                                    ->content('Rp ' . number_format((float) $record->denda_keterlambatan, 0, ',', '.')),

                                Placeholder::make('denda_kerusakan')
                                    ->label('Denda Kerusakan')
                                    ->content('Rp ' . number_format((float) $record->denda_kerusakan, 0, ',', '.')),

                                Placeholder::make('denda_kehilangan')
                                    ->label('Denda Kehilangan')
                                    ->content('Rp ' . number_format((float) $record->denda_kehilangan, 0, ',', '.')),
                            ]),

                        Placeholder::make('total_denda')
                            ->label('Total Denda')
                            ->content('Rp ' . number_format((float) $record->total_denda, 0, ',', '.'))
                            ->extraAttributes(['class' => 'text-2xl font-bold text-danger-600 dark:text-danger-400']),
                    ]),

                Section::make('Pembayaran')
                    ->description($hasPendingCash
                        ? 'Pembayaran cash menunggu verifikasi petugas'
                        : 'Pilih metode pembayaran untuk menyelesaikan denda')
                    ->icon('heroicon-o-credit-card')
                    ->schema(array_filter([
                        $hasPendingCash
                        ? Placeholder::make('cash_status')
                            ->label('Status')
                            ->content(new HtmlString(
                                '<span class="fi-badge flex items-center justify-center gap-x-1 rounded-md text-xs font-medium ring-1 ring-inset px-2 min-w-[theme(spacing.6)] py-1 fi-color-custom fi-color-warning bg-custom-50 text-custom-600 ring-custom-600/10 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30" style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);">⏳ Menunggu Verifikasi Cash</span>'
                            ))
                        : null,

                        !$hasPendingCash
                        ? ViewField::make('payment_buttons')
                            ->view('filament.peminjam.pages.payment-button')
                            ->viewData([
                                'snapToken' => $this->snapToken,
                                'recordId' => $this->recordId,
                            ])
                        : null,
                    ])),
            ])
            ->statePath('data');
    }
}