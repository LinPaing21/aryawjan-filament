<?php

namespace Stella\Pharmacy\Filament\Resources\Medicines\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Stella\Pharmacy\Filament\Resources\Medicines\MedicineResource;

class ListMedicines extends ListRecords
{
    protected static string $resource = MedicineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
