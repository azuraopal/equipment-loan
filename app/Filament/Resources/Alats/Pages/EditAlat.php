<?php

namespace App\Filament\Resources\Alats\Pages;

use App\Filament\Resources\Alats\AlatResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAlat extends EditRecord
{
    protected static string $resource = AlatResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
