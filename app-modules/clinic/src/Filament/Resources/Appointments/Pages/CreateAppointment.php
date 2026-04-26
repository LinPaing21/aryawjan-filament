<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Pages;

use Filament\Resources\Pages\CreateRecord;
use Stella\Clinic\Filament\Resources\Appointments\AppointmentResource;

class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;
}
