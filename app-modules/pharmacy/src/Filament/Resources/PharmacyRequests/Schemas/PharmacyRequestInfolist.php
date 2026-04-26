<?php

namespace Stella\Pharmacy\Filament\Resources\PharmacyRequests\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PharmacyRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('triageLog.id')
                    ->label('Triage Log ID'),
                TextEntry::make('patient.name')
                    ->label('Patient Name'),
                TextEntry::make('pharmacist.name')
                    ->label('Pharmacist Name')
                    ->placeholder('-'),
                TextEntry::make('status')
                    ->badge()
                    ->colors([
                        'warning' => 'pending',
                        'success' => fn ($state) => in_array($state, ['approved', 'completed']),
                        'danger' => 'rejected',
                    ]),
                TextEntry::make('suggested_medicines')
                    ->badge()
                    ->separator(',')
                    ->columnSpanFull(),
                TextEntry::make('approved_medicines')
                    ->badge()
                    ->separator(',')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
