<?php

namespace App\Filament\Admin\Resources\Peminjaman\Pages;

use App\Filament\Admin\Resources\PeminjamanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
