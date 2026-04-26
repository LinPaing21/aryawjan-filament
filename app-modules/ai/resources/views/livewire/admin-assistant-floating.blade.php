<div
    x-data="{ open: false }"
    class="fixed bottom-6 right-6 z-50 flex flex-col items-end gap-3"
>
    @if(auth()->check() && auth()->user()->hasRole('Admin'))
        {{-- Floating Panel --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-2"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100 translate-y-0"
            x-transition:leave-end="opacity-0 scale-95 translate-y-2"
            x-cloak
            class="w-[380px] bg-white dark:bg-gray-900 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700 flex flex-col overflow-hidden"
            style="max-height: 520px;"
        >
            {{-- Header --}}
            <div class="flex items-center justify-between px-4 py-3 bg-amber-600 text-white rounded-t-2xl shrink-0">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-s-chart-bar" class="w-5 h-5" />
                    <span class="font-semibold text-sm">Admin Assistant</span>
                </div>
                <button
                    type="button"
                    @click="open = false"
                    class="p-1 rounded-lg hover:bg-amber-700 transition-colors"
                >
                    <x-filament::icon icon="heroicon-m-x-mark" class="w-4 h-4" />
                </button>
            </div>

            {{-- Chat History --}}
            <div
                x-data="{
                    scrollToBottom() {
                        $el.scrollTo({ top: $el.scrollHeight, behavior: 'smooth' })
                    }
                }"
                x-init="scrollToBottom()"
                x-on:floating-message-sent.window="scrollToBottom()"
                class="flex-1 overflow-y-auto space-y-3 p-3 bg-gray-50/50 dark:bg-gray-950/50 min-h-[180px]"
            >
                @forelse($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="flex max-w-[90%] gap-2 {{ $message['role'] === 'user' ? 'flex-row-reverse' : 'flex-row' }}">
                            <div class="shrink-0 mt-1">
                                @if($message['role'] === 'assistant')
                                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white ring-1 ring-white dark:ring-gray-800">
                                        <x-filament::icon icon="heroicon-s-chart-bar" class="w-3.5 h-3.5" />
                                    </div>
                                @else
                                    <div class="flex items-center justify-center w-6 h-6 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 ring-1 ring-white dark:ring-gray-800">
                                        <x-filament::icon icon="heroicon-s-user" class="w-3.5 h-3.5" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex flex-col {{ $message['role'] === 'user' ? 'items-end' : 'items-start' }}">
                                <div class="px-3 py-2 rounded-xl shadow-sm text-sm {{
                                    $message['role'] === 'user'
                                        ? 'bg-amber-600 text-white rounded-tr-none'
                                        : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none text-gray-900 dark:text-gray-100'
                                }}">
                                    <div @class([
                                        'prose prose-xs dark:prose-invert max-w-none leading-relaxed',
                                        'text-white prose-headings:text-white prose-p:text-white prose-strong:text-white prose-a:text-white prose-li:text-white' => $message['role'] === 'user',
                                    ])>
                                        {!! str($message['content'])->markdown() !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-10 text-gray-400 dark:text-gray-600 opacity-50">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-10 w-10 mb-2" />
                        <p class="text-xs text-center">Ask about inventory, revenue,<br>appointments, or triage stats.</p>
                    </div>
                @endforelse

                {{-- Streaming State --}}
                @if($isStreaming)
                <div class="flex justify-start">
                    <div class="flex flex-row gap-2 max-w-[90%]">
                        <div class="shrink-0 mt-1">
                            <div class="flex items-center justify-center w-6 h-6 rounded-full bg-amber-600 text-white ring-1 ring-white dark:ring-gray-800 animate-pulse">
                                <x-filament::icon icon="heroicon-s-chart-bar" class="w-3.5 h-3.5" />
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="px-3 py-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none shadow-sm text-gray-900 dark:text-gray-100 min-w-[40px]">
                                <div class="prose prose-xs dark:prose-invert max-w-none leading-relaxed text-sm" wire:stream="floatingStreamContent"></div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- Quick Prompts --}}
            <div class="px-3 py-2 flex flex-wrap gap-1.5 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
                @foreach([
                    ['label' => 'Low stock', 'prompt' => 'Show me all medicines that are low in stock or out of stock.'],
                    ['label' => "Today's revenue", 'prompt' => 'Give me a revenue summary for today.'],
                    ['label' => 'Appointments', 'prompt' => 'Show me appointment statistics for this month.'],
                    ['label' => 'Triage stats', 'prompt' => 'What are the triage statistics for this month?'],
                ] as $chip)
                    <button
                        type="button"
                        wire:click="sendQuickPrompt('{{ addslashes($chip['prompt']) }}')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800 hover:bg-amber-100 dark:hover:bg-amber-900/40 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ $chip['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Input Form --}}
            <div class="px-3 pb-3 pt-2 border-t border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shrink-0">
                <form wire:submit="submit">
                    <div class="flex gap-2 items-end">
                        <div class="flex-1">
                            {{ $this->form }}
                        </div>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="shrink-0 mb-0.5 p-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        >
                            <x-filament::icon icon="heroicon-m-paper-airplane" class="w-4 h-4" />
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Toggle Button --}}
        <button
            type="button"
            @click="open = !open"
            class="flex items-center justify-center w-14 h-14 rounded-full bg-amber-600 hover:bg-amber-700 text-white shadow-lg hover:shadow-xl transition-all duration-200 hover:scale-105"
        >
            <span x-show="!open">
                <x-filament::icon icon="heroicon-s-chat-bubble-left-ellipsis" class="w-6 h-6" />
            </span>
            <span x-show="open" x-cloak>
                <x-filament::icon icon="heroicon-s-chevron-down" class="w-6 h-6" />
            </span>
        </button>
    @endif
</div>
