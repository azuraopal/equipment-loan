<?php

namespace App\Filament\Petugas\Resources\Pengembalian\Pages;

use App\Filament\Petugas\Resources\Pengembalian\PengembalianResource;
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
