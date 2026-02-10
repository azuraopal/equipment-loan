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
use BackedEnum;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
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
        return 'Selesaikan pembayaran denda pengembalian terlambat melalui Midtrans';
    }

    public $recordId;
    public $snapToken;
    public ?array $data = [];

    public function getRecordProperty()
    {
        return Pengembalian::with('peminjaman.alats')->find($this->recordId);
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

        $this->snapToken = app(PaymentService::class)->createPayment($record);

        if (empty($this->snapToken)) {
            return redirect()->route('filament.peminjam.resources.pengembalian.index');
        }

    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Peminjaman')
                            ->description('Detail peminjaman yang dikenakan denda')
                            ->icon('heroicon-o-document-text')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('nomor_peminjaman')
                                            ->label('No. Peminjaman')
                                            ->content($this->record->peminjaman->nomor_peminjaman)
                                            ->icon('heroicon-o-hashtag'),

                                        Placeholder::make('tanggal_pinjam')
                                            ->label('Tanggal Pinjam')
                                            ->content($this->record->peminjaman->tanggal_pinjam)
                                            ->icon('heroicon-o-calendar'),

                                        Placeholder::make('tanggal_kembali')
                                            ->label('Tanggal Kembali')
                                            ->content($this->record->peminjaman->tanggal_kembali)
                                            ->icon('heroicon-o-calendar-days'),

                                        Placeholder::make('status_pembayaran')
                                            ->label('Status Pembayaran')
                                            ->content($this->record->status_pembayaran)
                                            ->icon('heroicon-o-clock')
                                            ->extraAttributes(['class' => 'font-semibold']),
                                    ]),

                                ViewField::make('alats')
                                    ->label('Daftar Barang yang Dipinjam')
                                    ->view('filament.peminjam.pages.items-list')
                                    ->viewData([
                                        'record' => $this->record,
                                    ]),
                            ])
                            ->collapsible(),

                        Section::make('Tagihan & Pembayaran')
                            ->description('Lakukan pembayaran denda secara online')
                            ->icon('heroicon-o-currency-dollar')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('total_denda')
                                    ->label('Total Denda')
                                    ->content('Rp ' . number_format($this->record->total_denda, 0, ',', '.'))
                                    ->extraAttributes(['class' => 'text-2xl font-bold text-danger-600 dark:text-danger-400']),

                                ViewField::make('payment_button')
                                    ->view('filament.peminjam.pages.payment-button')
                                    ->viewData([
                                        'snapToken' => $this->snapToken,
                                    ]),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}