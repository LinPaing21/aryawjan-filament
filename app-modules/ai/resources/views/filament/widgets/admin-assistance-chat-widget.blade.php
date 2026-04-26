<x-filament-widgets::widget>
    <x-filament::section heading="Admin Assistant" description="Manage inventory, review revenue, and monitor clinic operations with AI.">
        <div class="flex flex-col gap-6">
            {{-- Chat History --}}
            <div
                x-data="{
                    scrollToBottom() {
                        $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' })
                    }
                }"
                x-init="scrollToBottom()"
                x-on:message-sent.window="scrollToBottom()"
                class="min-h-[300px] max-h-[600px] overflow-y-auto space-y-4 p-4 bg-gray-50/50 dark:bg-gray-950/50 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-inner"
            >
                @forelse($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="flex max-w-[85%] gap-3 {{ $message['role'] === 'user' ? 'flex-row-reverse' : 'flex-row' }}">
                            {{-- Avatar --}}
                            <div class="shrink-0 mt-1">
                                @if($message['role'] === 'assistant')
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-600 text-white shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        <x-filament::icon icon="heroicon-s-chart-bar" class="w-5 h-5" />
                                    </div>
                                @else
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        <x-filament::icon icon="heroicon-s-user" class="w-5 h-5" />
                                    </div>
                                @endif
                            </div>

                            {{-- Bubble --}}
                            <div class="flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }}">
                                <div class="px-4 py-2.5 rounded-2xl shadow-sm {{
                                    $message['role'] === 'user'
                                        ? 'bg-amber-600 text-white rounded-tr-none'
                                        : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none text-gray-900 dark:text-gray-100'
                                }}">
                                    <div @class([
                                        'prose prose-sm dark:prose-invert max-w-none leading-relaxed',
                                        'text-white prose-headings:text-white prose-p:text-white prose-strong:text-white prose-a:text-white prose-li:text-white prose-ul:text-white prose-table:text-white' => $message['role'] === 'user',
                                    ])>
                                        {!! str($message['content'])->markdown() !!}
                                    </div>
                                </div>
                                <span class="text-[10px] mt-1 opacity-50 px-1 font-bold uppercase tracking-widest">
                                    {{ $message['role'] === 'user' ? 'You' : 'AI Admin Assistant' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-600 opacity-40">
                        <x-filament::icon icon="heroicon-o-chart-bar-square" class="h-16 w-16 mb-4" />
                        <p class="text-sm font-medium">No conversation yet. Use a quick prompt or type your question below.</p>
                    </div>
                @endforelse

                {{-- Streaming State --}}
                @if($isStreaming)
                <div class="flex justify-start">
                    <div class="flex max-w-[85%] gap-3 flex-row">
                        <div class="shrink-0 mt-1">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-600 text-white shadow-sm ring-2 ring-white dark:ring-gray-800 animate-pulse">
                                <x-filament::icon icon="heroicon-s-chart-bar" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="px-4 py-2.5 rounded-2xl shadow-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none text-gray-900 dark:text-gray-100 min-w-[60px]">
                                <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed" wire:stream="streamContent"></div>
                            </div>
                            <span class="text-[10px] mt-1 opacity-50 px-1 font-bold uppercase tracking-widest">AI Admin Assistant</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Quick Prompt Chips --}}
            <div class="flex flex-wrap gap-2">
                @foreach([
                    ['label' => 'Low stock report', 'icon' => 'heroicon-m-archive-box-x-mark', 'prompt' => 'Show me all medicines that are low in stock or out of stock.'],
                    ['label' => "Today's revenue", 'icon' => 'heroicon-m-banknotes', 'prompt' => 'Give me a revenue summary for today.'],
                    ['label' => 'Appointment summary', 'icon' => 'heroicon-m-calendar-days', 'prompt' => 'Show me appointment statistics for this month.'],
                    ['label' => 'Triage stats', 'icon' => 'heroicon-m-chart-pie', 'prompt' => 'What are the triage statistics for this month?'],
                    ['label' => 'Monthly revenue', 'icon' => 'heroicon-m-arrow-trending-up', 'prompt' => 'Give me a full revenue report for this month including paid and unpaid invoices.'],
                ] as $chip)
                    <button
                        type="button"
                        wire:click="sendQuickPrompt('{{ addslashes($chip['prompt']) }}')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <x-filament::icon :icon="$chip['icon']" class="w-3.5 h-3.5" />
                        {{ $chip['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Input Form --}}
            <form wire:submit="submit" class="pt-4 border-t border-gray-100 dark:border-gray-800">
                <div class="space-y-4">
                    {{ $this->form }}

                    <div class="flex justify-end pt-2">
                        <x-filament::button
                            type="submit"
                            size="lg"
                            icon="heroicon-m-paper-airplane"
                            icon-position="after"
                            color="warning"
                            class="shadow-md"
                        >
                            Send
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
