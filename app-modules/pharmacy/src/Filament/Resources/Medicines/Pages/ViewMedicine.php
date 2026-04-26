<?php

namespace Stella\Pharmacy\Filament\Resources\Medicines\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Stella\Pharmacy\Filament\Resources\Medicines\MedicineResource;

class ViewMedicine extends ViewRecord
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
