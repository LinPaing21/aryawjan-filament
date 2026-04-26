<?php

namespace Stella\Ai\Filament\Resources\TriageLogs\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Flex;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Icon;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Enums\TriageStatus;
use Stella\Ai\Services\TriageService;
use Stella\Users\Filament\Resources\Patients\Schemas\PatientForm;

class TriageLogForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('patient_id')
                    ->relationship('patient', 'name')
                    ->createOptionForm(PatientForm::getForm())
                    ->required(),
                FusedGroup::make([
                    FileUpload::make('attachments')
                        ->label('File Attachments')
                        ->directory('triage_logs')
                        ->multiple()
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'text/plain'])
                        ->maxSize(10240)
                        ->required(fn (Get $get) => empty($get('raw_symptoms')))
                        ->live(),
                    Textarea::make('raw_symptoms')
                        ->required(fn (Get $get) => empty($get('attachments')))
                        ->rows(3)
                        ->columnSpanFull()
                        ->live(),
                ])
                    ->label('Symptoms')
                    ->columnSpanFull()
                    ->belowContent(Schema::between([
                            Flex::make([
                                Icon::make(Heroicon::LightBulb)->grow(false),
                                'Add patient symptoms or an attachment to analyze the severity.'
                            ]),
                            Action::make('analyze')
                                ->button()
                                ->action(function (Get $schemaGet, Set $schemaSet, TriageService $triageService) {
                                    // dd($schemaGet('ai_analysis'));
                                    $patientId = $schemaGet('patient_id');
                                    $rawSymptoms = $schemaGet('raw_symptoms');
                                    $attachments = $schemaGet('attachments');

                                    if (! $patientId || (! $rawSymptoms && ! $attachments)) {
                                        Notification::make()
                                            ->title('Analysis Failed')
                                            ->body('Please select a patient and provide symptoms or an attachment to analyze.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }

                                    logger($attachments);
                                    $analysis = $triageService->analyzeSymptoms($rawSymptoms, $patientId, $attachments);

                                    $schemaSet('ai_analysis', $analysis);

                                    $schemaSet('severity', $analysis['severity']);
                                            // Map category to status
                                    $status = match ($analysis['category']) {
                                        'pharmacy' => TriageStatus::TO_PHARMACY,
                                        'emergency', 'consultation' => TriageStatus::TO_DOCTOR,
                                        default => TriageStatus::PENDING,
                                    };
                                    $schemaSet('status', $status);
                                })
                        ])),
                KeyValue::make('ai_analysis')
                    ->required()
                    ->columnSpanFull()
                    ->addable(false)
                    ->deletable(false)
                    ->editableKeys(false)
                    ->editableValues(false)
                    ->extraAttributes([
                        'class' => '[&_th:nth-child(1)]:w-1/4 [&_td:nth-child(1)]:w-1/4',
                    ])
                    ->aboveContent([
                        fn ($state) => data_get($state, '3.value', '')
                    ])
                    ->belowContent([
                        Icon::make(Heroicon::LightBulb),
                        'This field is generated from symptoms analysis.'
                    ]),
                Select::make('severity')
                    ->options(Severity::class)
                    ->default('low')
                    ->required(),
                Select::make('status')
                    ->options(TriageStatus::class)
                    ->default('pending')
                    ->required(),
            ]);
    }
}
