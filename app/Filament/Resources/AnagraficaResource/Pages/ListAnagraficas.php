<?php

namespace App\Filament\Resources\AnagraficaResource\Pages;

use App\Filament\Resources\AnagraficaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAnagraficas extends ListRecords
{
    protected static string $resource = AnagraficaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
