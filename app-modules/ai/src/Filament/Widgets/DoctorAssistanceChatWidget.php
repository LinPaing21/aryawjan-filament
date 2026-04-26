<?php

namespace Stella\Ai\Filament\Widgets;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Laravel\Ai\Streaming\Events\TextDelta;
use Stella\Ai\Agents\ClinicAssistance;

class DoctorAssistanceChatWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'ai::filament.widgets.ai-chat-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public array $messages = [];

    public bool $isStreaming = false;

    public string $pendingMessage = '';

    public array $pendingAttachments = [];

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Doctor');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                FusedGroup::make([
                    FileUpload::make('attachments')
                        ->label('File Attachments')
                        ->acceptedFileTypes(['application/pdf', 'image/*', 'text/plain'])
                        ->maxSize(10240),
                    Textarea::make('message')
                        ->label('Message')
                        ->rows(4)
                        ->placeholder('Ask the AI clinic assistant...')
                        ->columnSpan('full')
                        ->required(),
                ])
                ->columns(1)
                ->columnSpanFull()
                ->label('Message'),
            ])
            ->columns(2)
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $this->pendingMessage = $data['message'];
        $this->pendingAttachments = $data['attachments'] ?? [];
        $this->messages[] = ['role' => 'user', 'content' => $this->pendingMessage];
        $this->isStreaming = true;

        $this->form->fill();
        $this->dispatch('message-sent');
        $this->js('$wire.streamResponse()');
    }

    public function streamResponse(): void
    {
        $streamable = (new ClinicAssistance)->stream(
            'User Message: ' . $this->pendingMessage,
            attachments: $this->pendingAttachments,
        );

        $fullText = '';

        foreach ($streamable as $event) {
            if ($event instanceof TextDelta) {
                $fullText .= $event->delta;
                $this->stream(content: $event->delta, to: 'streamContent');
            }
        }

        $this->messages[] = ['role' => 'assistant', 'content' => $fullText];
        $this->isStreaming = false;
        $this->pendingMessage = '';
        $this->pendingAttachments = [];

        $this->dispatch('message-sent');
    }

    public function sendQuickPrompt(string $prompt): void
    {
        $this->pendingMessage = $prompt;
        $this->messages[] = ['role' => 'user', 'content' => $prompt];
        $this->isStreaming = true;

        $this->dispatch('message-sent');
        $this->js('$wire.streamResponse()');
    }
}
