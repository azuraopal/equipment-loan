<?php

namespace App\Filament\Admin\Resources\Alats\Pages;

use App\Filament\Admin\Resources\AlatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlat extends EditRecord
{
    protected static string $resource = AlatResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
