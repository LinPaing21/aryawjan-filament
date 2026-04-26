<?php

namespace Stella\Ai\Filament\Resources\TriageLogs\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Stella\Ai\Filament\Resources\TriageLogs\TriageLogResource;

class EditTriageLog extends EditRecord
{
    protected static string $resource = TriageLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
