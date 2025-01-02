<?php

namespace App\Filament\Resources\FestivitaResource\Pages;

use App\Filament\Resources\FestivitaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFestivita extends EditRecord
{
    protected static string $resource = FestivitaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
