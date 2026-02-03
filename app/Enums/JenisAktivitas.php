<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;

enum JenisAktivitas: string implements HasLabel, HasColor
{
    case INSERT = 'INSERT';
    case UPDATE = 'UPDATE';
    case DELETE = 'DELETE';
    case LOGIN = 'LOGIN';
    case LOGOUT = 'LOGOUT';
    case APPROVE = 'APPROVE';
    case REJECT = 'REJECT';
    case KEMBALI = 'KEMBALI';

    public function getLabel(): ?string
    {
        return $this->name;
    }

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::INSERT, self::LOGIN, self::APPROVE => 'success',
            self::UPDATE, self::KEMBALI => 'warning',
            self::DELETE, self::LOGOUT, self::REJECT => 'danger',
        };
    }
}