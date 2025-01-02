<?php

namespace App\Filament\Resources\PresenzaResource\Pages;

use App\Filament\Resources\PresenzaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPresenza extends EditRecord
{
    protected static string $resource = PresenzaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
