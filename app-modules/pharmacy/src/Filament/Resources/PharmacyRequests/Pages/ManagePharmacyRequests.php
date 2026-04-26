<?php

namespace Stella\Pharmacy\Filament\Resources\PharmacyRequests\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Stella\Pharmacy\Filament\Resources\PharmacyRequests\PharmacyRequestResource;

class ManagePharmacyRequests extends ManageRecords
{
    protected static string $resource = PharmacyRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->modal(), // Ensure create action uses a modal as well
        ];
    }
}
