<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum KondisiAlat: string implements HasLabel, HasColor
{
    case Baik = 'Baik';
    case Rusak = 'Rusak';
    case Hilang = 'Hilang';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Baik => 'success',
            self::Rusak => 'warning',
            self::Hilang => 'danger',
        };
    }
}