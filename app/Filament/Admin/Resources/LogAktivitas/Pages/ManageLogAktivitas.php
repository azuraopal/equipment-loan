<?php

namespace App\Filament\Admin\Resources\LogAktivitas\Pages;

use App\Filament\Admin\Resources\LogAktivitasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageLogAktivitas extends ManageRecords
{
    protected static string $resource = LogAktivitasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
