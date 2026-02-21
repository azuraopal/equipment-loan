<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\Alats\Pages\CreateAlat;
use App\Filament\Admin\Resources\Alats\Pages\EditAlat;
use App\Filament\Admin\Resources\Alats\Pages\ListAlats;
use App\Models\Alat;
use App\Services\QrCodeService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\HtmlString;
use BackedEnum;
use UnitEnum;

class AlatResource extends Resource
{
    protected static ?string $model = Alat::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('nama_alat')
                    ->required()
                    ->maxLength(255)
                    ->columnSpan(2),
                Select::make('kategori_id')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('kode_alat')
                    ->placeholder('Otomatis oleh sistem')
                    ->disabled()
                    ->dehydrated(false)
                    ->visibleOn('edit'),
                TextInput::make('stok')
                    ->numeric()
                    ->default(0)
                    ->required(),
                TextInput::make('harga_satuan')
                    ->numeric()
                    ->mask(RawJs::make('$money($input, \',\', \'.\')'))
                    ->stripCharacters('.')
                    ->numeric()
                    ->prefix('Rp'),
                Select::make('kondisi_awal')
                    ->options([
                        'Baik' => 'Baik',
                        'Rusak' => 'Rusak',
                        'Hilang' => 'Hilang',
                    ])
                    ->native(false)
                    ->searchable()
                    ->preload()
                    ->default('Baik')
                    ->required(),
                Textarea::make('spesifikasi')
                    ->maxLength('500')
                    ->columnSpan(2),
                FileUpload::make('gambar')
                    ->disk('public')
                    ->directory('alat-gambar')
                    ->visibility('public')
                    ->image()
                    ->imagePreviewHeight('300')
                    ->columnSpanFull()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar'),
                TextColumn::make('nama_alat')->searchable(),
                TextColumn::make('kode_alat')->searchable(),
                TextColumn::make('kategori.nama_kategori')->badge(),
                TextColumn::make('stok')->label('Stok'),
                TextColumn::make('kondisi_awal')->badge(),
                TextColumn::make('spesifikasi'),
            ])
            ->actions([
                Action::make('lihatQr')
                    ->label('QR Code')
                    ->icon('heroicon-o-qr-code')
                    ->color('info')
                    ->modalHeading(fn(Alat $record) => 'QR Code: ' . $record->nama_alat)
                    ->modalDescription(fn(Alat $record) => 'Scan QR ini untuk melihat info alat ' . $record->kode_alat)
                    ->modalContent(function (Alat $record) {
                        $url = url("/alat/info/{$record->kode_alat}");
                        $qrCode = QrCodeService::generateSvg($url, 250);

                        $html = '<div style="text-align:center; padding:20px;">';
                        $html .= '<div style="display:inline-block; padding:24px; background:#fff; border-radius:16px; margin-bottom:16px;">';
                        $html .= $qrCode;
                        $html .= '</div>';
                        $html .= '<div style="margin-top:12px;">';
                        $html .= '<p style="font-size:18px; font-weight:700; margin-bottom:4px;">' . e($record->nama_alat) . '</p>';
                        $html .= '<p style="font-size:13px; color:#64748b; margin-bottom:16px;">' . e($record->kode_alat) . '</p>';
                        $html .= '<a href="' . $url . '" target="_blank" style="display:inline-flex; align-items:center; gap:6px; padding:8px 20px; background:linear-gradient(135deg,#3b82f6,#8b5cf6); color:#fff; border-radius:12px; text-decoration:none; font-size:13px; font-weight:600;">';
                        $html .= '🔗 Buka Halaman Info Alat</a>';
                        $html .= '</div></div>';

                        return new HtmlString($html);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAlats::route('/'),
            'create' => CreateAlat::route('/create'),
            'edit' => EditAlat::route('/{record}/edit'),
        ];
    }
}