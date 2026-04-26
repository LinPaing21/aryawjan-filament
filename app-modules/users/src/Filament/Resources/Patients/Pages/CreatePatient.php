<?php

namespace Stella\Users\Filament\Resources\Patients\Pages;

use Filament\Resources\Pages\CreateRecord;
use Stella\Users\Filament\Resources\Patients\PatientResource;

class CreatePatient extends CreateRecord
{
    protected static string $resource = PatientResource::class;

}
