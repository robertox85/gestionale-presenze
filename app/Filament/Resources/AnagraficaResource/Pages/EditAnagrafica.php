<?php

namespace App\Filament\Resources\AnagraficaResource\Pages;

use App\Filament\Resources\AnagraficaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAnagrafica extends EditRecord
{
    protected static string $resource = AnagraficaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
