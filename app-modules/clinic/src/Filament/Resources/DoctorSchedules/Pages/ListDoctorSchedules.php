<?php

namespace Stella\Clinic\Filament\Resources\DoctorSchedules\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Stella\Clinic\Filament\Resources\DoctorSchedules\DoctorScheduleResource;

class ListDoctorSchedules extends ListRecords
{
    protected static string $resource = DoctorScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
