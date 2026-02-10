<?php

namespace App\Filament\Peminjam\Resources\Peminjaman\Pages;

use App\Filament\Peminjam\Resources\Peminjaman\PeminjamanResource;
use App\Services\DendaService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePeminjaman extends CreateRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (DendaService::cekDendaBelumLunas(auth()->id())) {
            Notification::make()
                ->title('Peminjaman Ditolak')
                ->body('Anda masih memiliki denda yang belum lunas. Silakan lunasi denda terlebih dahulu sebelum meminjam kembali.')
                ->danger()
                ->persistent()
                ->send();

            $this->halt();
        }

        $data['user_id'] = auth()->id();
        $data['nomor_peminjaman'] = 'P-' . time();
        $data['status'] = 'Menunggu';
        return $data;
    }
}
