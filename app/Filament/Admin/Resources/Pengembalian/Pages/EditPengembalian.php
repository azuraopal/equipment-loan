<?php

namespace App\Filament\Admin\Resources\Pengembalian\Pages;

use App\Filament\Admin\Resources\PengembalianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPengembalian extends EditRecord
{
    protected static string $resource = PengembalianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
