<?php

namespace App\Filament\Peminjam\Resources\Peminjaman\Pages;

use App\Filament\Peminjam\Resources\Peminjaman\PeminjamanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPeminjaman extends ListRecords
{
    protected static string $resource = PeminjamanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
