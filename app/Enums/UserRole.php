<?php
namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum UserRole: string implements HasLabel, HasColor
{
    case Admin = 'Admin';
    case Petugas = 'Petugas';
    case Peminjam = 'Peminjam';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Petugas => 'warning',
            self::Peminjam => 'info',
        };
    }
}