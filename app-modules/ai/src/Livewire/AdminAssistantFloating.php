<?php

namespace Stella\Ai\Livewire;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Schemas\Schema;
use Laravel\Ai\Streaming\Events\TextDelta;
use Livewire\Component;
use Stella\Ai\Agents\AdminAssistance;

class AdminAssistantFloating extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public array $messages = [];

    public ?string $conversationId = null;

    public bool $isStreaming = false;

    public string $pendingMessage = '';

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Textarea::make('message')
                    ->label('')
                    ->rows(2)
                    ->placeholder('Ask about inventory, revenue, appointments...')
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
        $this->dispatch('floating-message-sent');
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
                $this->stream(content: $event->delta, to: 'floatingStreamContent');
            }
        }

        $this->conversationId = $streamable->conversationId ?? $this->conversationId;

        $this->messages[] = ['role' => 'assistant', 'content' => $fullText];
        $this->isStreaming = false;
        $this->pendingMessage = '';

        $this->dispatch('floating-message-sent');
    }

    public function sendQuickPrompt(string $prompt): void
    {
        $this->pendingMessage = $prompt;
        $this->messages[] = ['role' => 'user', 'content' => $prompt];
        $this->isStreaming = true;

        $this->dispatch('floating-message-sent');
        $this->js('$wire.streamResponse()');
    }

    public function render(): \Illuminate\View\View
    {
        return view('ai::livewire.admin-assistant-floating');
    }
}
