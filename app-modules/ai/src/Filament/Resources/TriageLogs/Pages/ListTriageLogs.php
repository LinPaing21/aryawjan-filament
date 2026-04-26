<?php

namespace Stella\Ai\Filament\Resources\TriageLogs\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Stella\Ai\Filament\Resources\TriageLogs\TriageLogResource;

class ListTriageLogs extends ListRecords
{
    protected static string $resource = TriageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
