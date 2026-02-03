<?php

namespace App\Filament\Peminjam\Resources\Peminjaman\Pages;

use App\Filament\Peminjam\Resources\Peminjaman\PeminjamanResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPeminjaman extends EditRecord
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
