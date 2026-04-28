<?php

namespace Stella\Ai\Filament\Widgets;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Enums\TriageStatus;
use Stella\Ai\Filament\Resources\TriageLogs\TriageLogResource;
use Stella\Ai\Models\TriageLog;
use Stella\Ai\Services\TriageService;

class PharmacyAssistanceChatWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'ai::filament.widgets.pharmacy-assistance-chat-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public array $messages = [];

    public ?string $conversationId = null;

    public ?int $patientId = null;

    protected TriageService $triageService;

    public function boot()
    {
        $this->triageService = app(TriageService::class);
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Pharmacist');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Select::make('patient_id')
                    ->options(fn() => \App\Models\User::doesntHave('roles')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn(?int $state) => $this->patientId = $state),
                FusedGroup::make([
                    FileUpload::make('attachment')
                        ->label('File Attachment')
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'text/plain'])
                        ->maxSize(10240)
                        ->columnSpanFull(),
                    Textarea::make('message')
                        ->label('Message')
                        ->placeholder('Ask the AI clinic assistant about symptoms...')
                        ->rows(4)
                        ->required(),
                    // FileUpload::make('audio')
                    //     ->label('Voice Message (Audio)')
                    //     ->acceptedFileTypes(['audio/*'])
                    //     ->maxSize(20480),
                ])
                ->label('Symptoms')
                ->columnSpanFull()
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();
        $userMessage = $data['message'];

        $this->messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // $triageService = new TriageService;
        $analysis = $this->triageService->analyzeSymptoms(
            $userMessage,
            patientId: $data['patient_id'],
            attachment: $data['attachment'] ?? null,
            conversationId: $this->conversationId
        );

        // Map category to status
        $status = match ($analysis['category']) {
            'pharmacy' => TriageStatus::TO_PHARMACY,
            'emergency', 'consultation' => TriageStatus::TO_DOCTOR,
            default => TriageStatus::PENDING,
        };

        $this->conversationId = $analysis['conversationId'];

        // Save triage log
        // TriageLog::create([
        //     'patient_id' => auth()->id(), // For widget context, we use current user as "patient" for now
        //     'raw_symptoms' => $userMessage,
        //     'ai_analysis' => $analysis,
        //     'severity' => match ($analysis['severity']) {
        //         'low' => Severity::LOW,
        //         'medium' => Severity::MEDIUM,
        //         'high' => Severity::High,
        //         default => Severity::LOW,
        //     },
        //     'status' => $status,
        // ]);

        $this->messages[] = [
            'role' => 'assistant',
            'content' => $analysis,
            'is_structured' => true,
        ];

        $this->form->fill();

        $this->dispatch('message-sent');
    }

    public function createLog()
    {
        // This method can be used to create a log entry based on the current conversation
        // For demonstration, we will just log the current messages and conversation ID

        logger('Creating Triage Log', [
            'conversation_id' => $this->conversationId,
            'messages' => $this->messages,
            'patient_id' => $this->patientId,
        ]);

        // $triageService = new TriageService;
        $analysis = $this->triageService->analyzeSymptoms(
            'Perform a triage analysis and clinical summary based on the patient symptoms discussed in this conversation.',
            patientId: $this->patientId,
            conversationId: $this->conversationId
        );

        logger('Triage Analysis for Log Creation', [
            'analysis' => $analysis,
        ]);

        // Map category to status
        $status = match ($analysis['category']) {
            'pharmacy' => TriageStatus::TO_PHARMACY,
            'emergency', 'consultation' => TriageStatus::TO_DOCTOR,
            default => TriageStatus::PENDING,
        };

        $userMessages = collect($this->messages)
            ->where('role', 'user')
            ->pluck('content')
            ->toArray();

        // Save triage log
        $triageLog = TriageLog::create([
            'patient_id' => $this->patientId,
            'raw_symptoms' => implode('\n', $userMessages),
            'ai_analysis' => $analysis,
            'severity' => match ($analysis['severity']) {
                'low' => Severity::LOW,
                'medium' => Severity::MEDIUM,
                'high' => Severity::HIGH,
                default => Severity::LOW,
            },
            'status' => $status,
        ]);

        return redirect(TriageLogResource::getUrl('edit', ['record' => $triageLog]));
    }
}
