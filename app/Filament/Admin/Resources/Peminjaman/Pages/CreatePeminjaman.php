<?php

namespace App\Filament\Admin\Resources\Peminjaman\Pages;

use App\Enums\PeminjamanStatus;
use App\Filament\Admin\Resources\PeminjamanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['nomor_peminjaman'] = 'P-' . time();
        $data['status'] = PeminjamanStatus::Menunggu;

        return $data;
    }
}
