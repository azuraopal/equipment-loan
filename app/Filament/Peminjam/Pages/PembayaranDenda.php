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

    public Pengembalian $record;
    public $snapToken;
    public ?array $data = [];

    public function mount(Pengembalian $record)
    {
        $this->record = $record;
        $paymentService = app(PaymentService::class);

        if ($this->record->peminjaman->user_id !== Auth::id()) {
            abort(403);
        }

        if ($this->record->status_pembayaran === 'Lunas') {
            return redirect()->route('filament.peminjam.resources.pengembalian.index');
        }

        try {
            $this->snapToken = $paymentService->createPayment($this->record);
        } catch (\Exception $e) {
            $this->addError('payment', $e->getMessage());
        }

        $this->form->fill([
            'nomor_peminjaman' => $this->record->peminjaman->nomor_peminjaman,
            'tanggal_pinjam' => $this->record->peminjaman->tanggal_pinjam->format('d M Y'),
            'tanggal_kembali' => $this->record->tanggal_kembali_real?->format('d M Y') ?? '-',
            'status_pembayaran' => $this->record->status_pembayaran === 'Belum_Lunas' ? 'Belum Lunas' : 'Lunas',
            'total_denda' => 'Rp ' . number_format($this->record->total_denda, 0, ',', '.'),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Informasi Peminjaman')
                            ->columnSpan(2)
                            ->schema([
                                Grid::make(2)
                                    ->schema([
                                        Placeholder::make('nomor_peminjaman')
                                            ->label('No. Peminjaman')
                                            ->content(fn($get) => $get('nomor_peminjaman'))
                                            ->icon('heroicon-o-hashtag'),

                                        Placeholder::make('tanggal_pinjam')
                                            ->label('Tanggal Pinjam')
                                            ->content(fn($get) => $get('tanggal_pinjam')),

                                        Placeholder::make('tanggal_kembali')
                                            ->label('Tanggal Kembali')
                                            ->content(fn($get) => $get('tanggal_kembali')),

                                        Placeholder::make('status_pembayaran')
                                            ->label('Status')
                                            ->content(fn($get) => $get('status_pembayaran'))
                                            ->extraAttributes(['class' => 'text-red-600 font-bold']),
                                    ]),

                                ViewField::make('alats')
                                    ->label('Daftar Barang')
                                    ->view('filament.peminjam.pages.items-list'),
                            ]),

                        Section::make('Tagihan')
                            ->columnSpan(1)
                            ->schema([
                                Placeholder::make('total_denda')
                                    ->label('Total Denda')
                                    ->content(fn($get) => $get('total_denda'))
                                    ->extraAttributes(['class' => 'text-3xl font-bold text-red-600']),

                                ViewField::make('payment_button')
                                    ->view('filament.peminjam.pages.payment-button')
                            ]),
                    ]),
            ])
            ->statePath('data');
    }
}