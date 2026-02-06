<?php

namespace App\Filament\Admin\Resources\Pengembalian\Pages;

use App\Filament\Admin\Resources\PengembalianResource;
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
