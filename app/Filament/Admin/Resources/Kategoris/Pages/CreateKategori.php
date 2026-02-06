<?php

namespace App\Filament\Admin\Resources\Kategoris\Pages;

use App\Filament\Admin\Resources\KategoriResource;
use Filament\Resources\Pages\CreateRecord;

class CreateKategori extends CreateRecord
{
    protected static string $resource = KategoriResource::class;


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
