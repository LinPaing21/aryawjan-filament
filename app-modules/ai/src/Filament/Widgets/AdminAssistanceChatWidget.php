<?php

namespace Stella\Ai\Filament\Widgets;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Laravel\Ai\Streaming\Events\TextDelta;
use Stella\Ai\Agents\AdminAssistance;

class AdminAssistanceChatWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected string $view = 'ai::filament.widgets.admin-assistance-chat-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $isStreaming = false;

    public string $pendingMessage = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public static function canView(): bool
    {
        return auth()->user()->hasRole('Admin');
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('Message')
                    ->rows(3)
                    ->placeholder('Ask about inventory, revenue, appointments, or triage stats...')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        $this->pendingMessage = $data['message'];
        $this->messages[] = ['role' => 'user', 'content' => $this->pendingMessage];
        $this->isStreaming = true;

        $this->form->fill();
        $this->dispatch('message-sent');
        $this->js('$wire.streamResponse()');
    }

    public function streamResponse(): void
    {
        $agent = new AdminAssistance;

        if ($this->conversationId) {
            $agent = $agent->continue($this->conversationId, as: auth()->user());
        } else {
            $agent = $agent->forUser(auth()->user());
        }

        $streamable = $agent->stream('Today Date: ' . now()->format('Y-m-d') . ' ' . $this->pendingMessage);

        $fullText = '';

        foreach ($streamable as $event) {
            if ($event instanceof TextDelta) {
                $fullText .= $event->delta;
                $this->stream(content: $event->delta, to: 'streamContent');
            }
        }

        $this->conversationId = $streamable->conversationId ?? $this->conversationId;

        $this->messages[] = ['role' => 'assistant', 'content' => $fullText];
        $this->isStreaming = false;
        $this->pendingMessage = '';

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
