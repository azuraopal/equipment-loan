<?php

namespace App\Filament\Peminjam\Resources\Pengembalian\Pages;

use App\Filament\Peminjam\Resources\Pengembalian\PengembalianResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPengembalian extends ListRecords
{
    protected static string $resource = PengembalianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
