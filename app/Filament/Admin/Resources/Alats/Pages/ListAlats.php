<?php

namespace App\Filament\Admin\Resources\Alats\Pages;

use App\Filament\Admin\Resources\AlatResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAlats extends ListRecords
{
    protected static string $resource = AlatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
