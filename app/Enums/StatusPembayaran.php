<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum StatusPembayaran: string implements HasLabel, HasColor
{
    case Lunas = 'Lunas';
    case Belum_Lunas = 'Belum_Lunas';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Lunas => 'Lunas',
            self::Belum_Lunas => 'Belum Lunas',
        };
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Lunas => 'success',
            self::Belum_Lunas => 'danger',
        };
    }
}