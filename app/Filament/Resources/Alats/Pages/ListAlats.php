<?php

namespace App\Filament\Resources\Alats\Pages;

use App\Filament\Resources\Alats\AlatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlats extends ListRecords
{
    protected static string $resource = AlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
