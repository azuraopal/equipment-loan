<?php

namespace App\Filament\Peminjam\Resources\Peminjaman\Pages;

use App\Filament\Peminjam\Resources\Peminjaman\PeminjamanResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['user_id'] = auth()->id();
        $data['nomor_peminjaman'] = 'P-' . time();
        $data['status'] = 'Menunggu';
        return $data;
    }
}
