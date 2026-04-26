<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MedicalRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('patient_id')
                    ->numeric(),
                TextEntry::make('doctor_id')
                    ->numeric(),
                TextEntry::make('symptoms')
                    ->columnSpanFull(),
                TextEntry::make('physical_examination')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('ai_suggestions.reasoning')
                    ->label('AI Suggestions')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('final_diagnosis'),
                TextEntry::make('doctor_notes')
                    ->placeholder('-')
                    ->columnSpanFull(),
                RepeatableEntry::make('prescriptions')
                    ->label('Prescriptions')
                    ->schema([
                        TextEntry::make('drug_name')
                            ->label('Drug Name')
                            ->default('-'),
                        TextEntry::make('dosage')
                            ->label('Dosage')
                            ->default('-'),
                        TextEntry::make('unit_price')
                            ->label('Unit Price')
                            ->default('-'),
                        TextEntry::make('quantity')
                            ->label('Quantity')
                            ->default('-'),
                    ])
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
