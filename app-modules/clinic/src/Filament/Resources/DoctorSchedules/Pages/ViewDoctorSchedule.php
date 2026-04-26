<?php

namespace Stella\Clinic\Filament\Resources\DoctorSchedules\Pages;

use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Stella\Clinic\Filament\Resources\DoctorSchedules\DoctorScheduleResource;

class ViewDoctorSchedule extends ViewRecord
{
    protected static string $resource = DoctorScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
