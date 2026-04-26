<?php

namespace Stella\Clinic\Filament\Resources\MedicalRecords\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MedicalRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('patient_id')
                    ->required()
                    ->numeric(),
                TextInput::make('doctor_id')
                    ->required()
                    ->numeric(),
                Textarea::make('symptoms')
                    ->required()
                    ->columnSpanFull(),
                Textarea::make('physical_examination')
                    ->columnSpanFull(),
                KeyValue::make('ai_suggestions'),
                TextInput::make('final_diagnosis')
                    ->required(),
                Textarea::make('doctor_notes')
                    ->columnSpanFull(),
                Repeater::make('prescriptions')
                    ->schema([
                        TextInput::make('drug_name')->required(),
                        TextInput::make('dosage'),
                        Toggle::make('is_available_in_clinic')
                            ->label('Available in Clinic')
                            ->default(true)
                            ->live(),

                        TextInput::make('unit_price')
                            ->numeric()
                            ->hidden(fn ($get) => !$get('is_available_in_clinic')),

                        TextInput::make('quantity')
                            ->numeric()
                            ->hidden(fn ($get) => !$get('is_available_in_clinic')),
                    ])
            ]);
    }
}
