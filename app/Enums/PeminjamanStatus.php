<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;

enum PeminjamanStatus: string implements HasLabel, HasColor, HasIcon
{
    case Menunggu = 'Menunggu';
    case Disetujui = 'Disetujui';
    case Ditolak = 'Ditolak';
    case Kembali = 'Kembali';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu Persetujuan',
            self::Disetujui => 'Sedang Dipinjam',
            self::Ditolak => 'Ditolak',
            self::Kembali => 'Sudah Dikembalikan',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Menunggu => 'warning',
            self::Disetujui => 'success',
            self::Ditolak => 'danger',
            self::Kembali => 'info',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::Menunggu => 'heroicon-m-clock',
            self::Disetujui => 'heroicon-m-check-circle',
            self::Ditolak => 'heroicon-m-x-circle',
            self::Kembali => 'heroicon-m-arrow-left-on-rectangle',
        };
    }
}