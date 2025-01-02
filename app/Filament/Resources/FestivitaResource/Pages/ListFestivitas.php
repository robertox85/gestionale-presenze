<?php

namespace App\Filament\Resources\FestivitaResource\Pages;

use App\Filament\Resources\FestivitaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFestivitas extends ListRecords
{
    protected static string $resource = FestivitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
