<?php

namespace App\Filament\Resources\SpecAttributeResource\Pages;

use App\Filament\Resources\SpecAttributeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSpecAttribute extends EditRecord
{
    protected static string $resource = SpecAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
