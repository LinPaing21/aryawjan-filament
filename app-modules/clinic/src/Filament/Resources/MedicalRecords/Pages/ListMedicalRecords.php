<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Stella\Clinic\Filament\Resources\MedicalRecords\MedicalRecordResource;

class ListMedicalRecords extends ListRecords
{
    protected static string $resource = MedicalRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
