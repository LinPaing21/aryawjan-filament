<x-filament-widgets::widget>
    <x-filament::section heading="Pharmacy Assistant" description="Analyze patient symptoms to determine care pathways and suggested medications.">
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
                            {{-- Avatar/Icon --}}
                            <div class="shrink-0 mt-1">
                                @if($message['role'] === 'assistant')
                                    <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-600 text-white shadow-sm ring-2 ring-white dark:ring-gray-800">
                                        <x-filament::icon icon="heroicon-s-sparkles" class="w-5 h-5" />
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
                                        ? 'bg-primary-600 text-white rounded-tr-none'
                                        : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none text-gray-900 dark:text-gray-100'
                                }}">
                                    @if(isset($message['is_structured']) && $message['is_structured'])
                                        <div class="space-y-4 min-w-[300px]">
                                            <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-2">
                                                <div class="flex items-center gap-2">
                                                    @php
                                                        $severityColor = match($message['content']['severity']) {
                                                            'high' => 'text-danger-600 dark:text-danger-400',
                                                            'medium' => 'text-warning-600 dark:text-warning-400',
                                                            default => 'text-success-600 dark:text-success-400',
                                                        };
                                                        $categoryIcon = match($message['content']['category']) {
                                                            'emergency' => 'heroicon-m-bolt',
                                                            'consultation' => 'heroicon-m-user-group',
                                                            'pharmacy' => 'heroicon-m-beaker',
                                                            default => 'heroicon-m-information-circle',
                                                        };
                                                    @endphp
                                                    <span class="text-xs font-bold uppercase tracking-wider {{ $severityColor }}">
                                                        {{ $message['content']['severity'] }} Severity
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-1 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-full">
                                                    <x-filament::icon :icon="$categoryIcon" class="w-3 h-3 text-gray-500" />
                                                    <span class="text-[10px] font-bold uppercase text-gray-500">
                                                        {{ $message['content']['category'] }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="prose prose-sm dark:prose-invert max-w-none">
                                                <p class="font-medium text-gray-900 dark:text-gray-100">
                                                    {{ $message['content']['reasoning'] }}
                                                </p>
                                            </div>

                                            @if(!empty($message['content']['suggested_meds']))
                                                <div class="bg-primary-50 dark:bg-primary-950/30 p-3 rounded-xl border border-primary-100 dark:border-primary-900/50">
                                                    <h4 class="text-[10px] font-bold uppercase text-primary-600 dark:text-primary-400 mb-2 tracking-widest">
                                                        Suggested Medications
                                                    </h4>
                                                    <ul class="space-y-1">
                                                        @foreach($message['content']['suggested_meds'] as $med)
                                                            <li class="flex items-center gap-2 text-sm text-primary-900 dark:text-primary-100 font-medium">
                                                                <x-filament::icon icon="heroicon-m-check-circle" class="w-4 h-4 text-primary-500" />
                                                                {{ $med }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div @class([
                                            'prose prose-sm dark:prose-invert max-w-none leading-relaxed',
                                            'text-white prose-headings:text-white prose-p:text-white prose-strong:text-white prose-a:text-white prose-li:text-white prose-ul:text-white' => $message['role'] === 'user',
                                        ])>
                                            {!! str($message['content'])->markdown() !!}
                                        </div>
                                    @endif
                                </div>
                                <span class="text-[10px] mt-1 opacity-50 px-1 font-bold uppercase tracking-widest">
                                    {{ $message['role'] === 'user' ? 'You' : 'AI Triage Assistant' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-600 opacity-40">
                        <x-filament::icon icon="heroicon-o-chat-bubble-bottom-center-text" class="h-16 w-16 mb-4" />
                        <p class="text-sm font-medium">No triage records yet. Enter patient symptoms to begin analysis.</p>
                    </div>
                @endforelse

                {{-- Thinking State --}}
                <div wire:loading wire:target="submit" class="flex justify-start">
                    <div class="flex flex-row gap-3 max-w-[85%]">
                        <div class="shrink-0">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-600 text-white shadow-sm ring-2 ring-white dark:ring-gray-800">
                                <x-filament::icon icon="heroicon-s-sparkles" class="w-5 h-5 animate-pulse" />
                            </div>
                        </div>
                        <div class="px-4 py-3 rounded-2xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none shadow-sm flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                            <span class="w-1.5 h-1.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                            <span class="w-1.5 h-1.5 bg-primary-600 dark:bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Input Form --}}
            <form wire:submit="submit" class="pt-6 border-t border-gray-100 dark:border-gray-800">
                <div class="space-y-4">
                    {{ $this->form }}

                    <div class="flex gap-5 justify-end pt-2">
                        <x-filament::button
                            type="button"
                            size="lg"
                            icon="heroicon-m-plus-circle"
                            icon-position="after"
                            class="shadow-md bg-green-400 hover:bg-green-500 focus:ring-green-300"
                            wire:click="createLog"
                        >
                            Create Log
                        </x-filament::button>
                        <x-filament::button
                            type="submit"
                            size="lg"
                            icon="heroicon-m-magnifying-glass"
                            icon-position="after"
                            class="shadow-md"
                        >
                            Analyze Symptoms
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
