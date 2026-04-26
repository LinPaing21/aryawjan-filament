<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Stella\Clinic\Filament\Resources\Appointments\AppointmentResource;

class ListAppointments extends ListRecords
{
    protected static string $resource = AppointmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction::make(),
        ];
    }

    // public function table(Table $table): Table
    // {
    //     return $table
    //         ->modifyQueryUsing(fn (Builder $query) => $query->selectRaw("*, split_part(token_number, '-', -1) as token_order"));
    // }
}
