<?php

namespace App\Filament\Resources\PresenzaResource\Pages;

use App\Filament\Resources\PresenzaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPresenzas extends ListRecords
{
    protected static string $resource = PresenzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
