<?php

namespace Stella\Clinic\Filament\Resources\Appointments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Services\AppointmentService;

class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->searchable()
                    ->required(),
                Select::make('doctor_id')
                    ->relationship('doctor', 'name')
                    ->searchable()
                    ->live(),
                Select::make('triage_log_id')
                    ->relationship('triageLog', 'id')
                    ->disabled()
                    ->live()
                    ->afterStateHydrated(function (mixed $state, Set $set, Get $get) {
                        $log = TriageLog::find($state);
                        if (! $log) {
                            return;
                        }

                        if (! $get('symptoms')) {
                            $set('symptoms', $log->raw_symptoms);
                        }

                        if (! $get('prescriptions') && ! empty($log->ai_analysis['suggested_meds'])) {
                            $repeaterData = [];
                            foreach ($log->ai_analysis['suggested_meds'] as $drug) {
                                $repeaterData[(string) Str::uuid()] = [
                                    'drug_name' => $drug,
                                    'dosage' => null,
                                    'is_available_in_clinic' => true,
                                    'unit_price' => null,
                                    'quantity' => 1,
                                ];
                            }
                            $set('prescriptions', $repeaterData);
                        }
                    }),
                DatePicker::make('scheduled_at')
                    ->native(false)
                    ->closeOnDateSelection()
                    // ->minDate(today())
                    ->maxDate(today()->addMonth())
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('doctor_id'))
                    ->disabledDates(function (Get $get, AppointmentService $appointmentService) {
                        $doctorId = $get('doctor_id');

                        return $appointmentService->getUnavailableDates($doctorId);
                    })
                    ->belowContent([
                        Icon::make('heroicon-o-information-circle')
                            ->color('primary'),
                        'Please select doctor to see available dates.',
                    ]),
                Select::make('status')
                    ->options([
                        'booked' => 'Booked',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('booked')
                    ->required()
                    ->live(),
                Section::make('Medical Record')
                    ->visible(fn (Get $get) => $get('status') === 'completed')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        Textarea::make('symptoms')
                            ->required()
                            ->dehydrated(false)
                            ->rows(5),
                        Textarea::make('final_diagnosis')
                            ->required()
                            ->rows(5)
                            ->dehydrated(false),
                        Textarea::make('doctor_notes')
                            ->dehydrated(false)
                            ->rows(5)
                            ->columnSpanFull(),
                        Repeater::make('prescriptions')
                            ->dehydrated(false)
                            ->schema([
                                TextInput::make('drug_name')->required(),
                                TextInput::make('dosage'),
                                Toggle::make('is_available_in_clinic')
                                    ->label('Available in Clinic')
                                    ->default(true)
                                    ->live(),
                                TextInput::make('unit_price')
                                    ->numeric()
                                    ->hidden(fn ($get) => ! $get('is_available_in_clinic')),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->hidden(fn ($get) => ! $get('is_available_in_clinic')),
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
