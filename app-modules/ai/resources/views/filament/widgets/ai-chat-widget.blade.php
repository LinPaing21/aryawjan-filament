<x-filament-widgets::widget>
    <x-filament::section heading="AI Clinic Assistant" description="Ask about patients, diagnoses, treatment protocols, and medicines.">
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
                class="min-h-75 max-h-150 overflow-y-auto space-y-4 p-4 bg-gray-50/50 dark:bg-gray-950/50 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-inner"
            >
                @forelse($messages as $message)
                    <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="flex max-w-[85%] gap-3 {{ $message['role'] === 'user' ? 'flex-row-reverse' : 'flex-row' }}">
                            {{-- Avatar --}}
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
                                    <div @class([
                                        'prose prose-sm dark:prose-invert max-w-none leading-relaxed',
                                        'text-white prose-headings:text-white prose-p:text-white prose-strong:text-white prose-a:text-white prose-li:text-white prose-ul:text-white prose-table:text-white' => $message['role'] === 'user',
                                    ])>
                                        {!! str($message['content'])->markdown() !!}
                                    </div>
                                </div>
                                <span class="text-[10px] mt-1 opacity-50 px-1 font-bold uppercase tracking-widest">
                                    {{ $message['role'] === 'user' ? 'You' : 'AI Clinic Assistant' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-20 text-gray-400 dark:text-gray-600 opacity-40">
                        <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-16 w-16 mb-4" />
                        <p class="text-sm font-medium">No conversation yet. Use a quick prompt or type your question below.</p>
                    </div>
                @endforelse

                {{-- Streaming State --}}
                @if($isStreaming)
                <div class="flex justify-start">
                    <div class="flex max-w-[85%] gap-3 flex-row">
                        <div class="shrink-0 mt-1">
                            <div class="flex items-center justify-center w-8 h-8 rounded-full bg-primary-600 text-white shadow-sm ring-2 ring-white dark:ring-gray-800 animate-pulse">
                                <x-filament::icon icon="heroicon-s-sparkles" class="w-5 h-5" />
                            </div>
                        </div>
                        <div class="flex flex-col items-start">
                            <div class="px-4 py-2.5 rounded-2xl shadow-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-tl-none text-gray-900 dark:text-gray-100 min-w-15">
                                <div class="prose prose-sm dark:prose-invert max-w-none leading-relaxed" wire:stream="streamContent"></div>
                            </div>
                            <span class="text-[10px] mt-1 opacity-50 px-1 font-bold uppercase tracking-widest">AI Clinic Assistant</span>
                        </div>
                    </div>
                </div>
                @endif
            </div>


            {{-- Quick Prompt Chips --}}
            <div class="flex flex-wrap gap-2">
                @foreach([
                    ['label' => 'Current patient', 'icon' => 'heroicon-m-user-circle', 'prompt' => 'Get the details of my current appointment patient.'],
                    ['label' => 'Patient history', 'icon' => 'heroicon-m-clipboard-document-list', 'prompt' => 'Retrieve the medical history of my current patient.'],
                    ['label' => 'Search medicines', 'icon' => 'heroicon-m-beaker', 'prompt' => 'What medicines are available for pain management?'],
                    ['label' => 'Treatment protocol', 'icon' => 'heroicon-m-book-open', 'prompt' => 'What is the standard treatment protocol for respiratory infections?'],
                    ['label' => 'Drug interactions', 'icon' => 'heroicon-m-exclamation-triangle', 'prompt' => 'Check for common drug interactions with amoxicillin and ibuprofen.'],
                ] as $chip)
                    <button
                        type="button"
                        wire:click="sendQuickPrompt('{{ addslashes($chip['prompt']) }}')"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-300 border border-primary-200 dark:border-primary-800 hover:bg-primary-100 dark:hover:bg-primary-900/40 transition-colors cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <x-filament::icon :icon="$chip['icon']" class="w-3.5 h-3.5" />
                        {{ $chip['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Input Form --}}
            <div
                x-data="{
                    isRecording: false,
                    lang: 'en-US',
                    recognition: null,
                    toggleLang() {
                        this.lang = this.lang === 'en-US' ? 'my-MM' : 'en-US';
                    },
                    startRecording() {
                        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
                        if (!SpeechRecognition) {
                            alert('Speech recognition is not supported in your browser.');
                            return;
                        }
                        this.recognition = new SpeechRecognition();
                        this.recognition.continuous = false;
                        this.recognition.interimResults = false;
                        this.recognition.lang = this.lang;
                        this.recognition.onresult = (e) => {
                            const transcript = e.results[0][0].transcript;
                            $wire.set('data.message', transcript).then(() => $wire.submit());
                            this.isRecording = false;
                        };
                        this.recognition.onerror = () => { this.isRecording = false; };
                        this.recognition.onend = () => { this.isRecording = false; };
                        this.recognition.start();
                        this.isRecording = true;
                    },
                    stopRecording() {
                        if (this.recognition) this.recognition.stop();
                        this.isRecording = false;
                    }
                }"
                class="pt-4 border-t border-gray-100 dark:border-gray-800"
            >
                <form wire:submit="submit">
                    <div class="space-y-3">
                        {{ $this->form }}

                        <div class="flex items-center justify-between pt-1">
                            {{-- Mic + Language Toggle --}}
                            <div class="flex items-center gap-2">
                                {{-- Language Toggle --}}
                                <button
                                    type="button"
                                    @click="toggleLang()"
                                    :title="'Switch to ' + (lang === 'en-US' ? 'Burmese' : 'English')"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold border transition-colors bg-gray-100 dark:bg-gray-700 border-gray-200 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600"
                                    x-text="lang === 'en-US' ? 'EN' : 'မြန်မာ'"
                                ></button>

                                {{-- Mic Button --}}
                                <button
                                    type="button"
                                    @click="isRecording ? stopRecording() : startRecording()"
                                    :title="isRecording ? 'Stop recording' : 'Speak to assistant (' + (lang === 'en-US' ? 'English' : 'Burmese') + ')'"
                                    :class="isRecording
                                        ? 'bg-red-500 hover:bg-red-600 ring-4 ring-red-200 dark:ring-red-900 animate-pulse text-white'
                                        : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'"
                                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition-all duration-200"
                                >
                                    <x-filament::icon
                                        icon="heroicon-m-microphone"
                                        ::class="isRecording ? 'text-white' : 'text-gray-500 dark:text-gray-400'"
                                        class="w-5 h-5"
                                    />
                                    <span
                                        x-text="isRecording ? 'Recording...' : 'Speak'"
                                        :class="isRecording ? 'text-white' : 'text-gray-600 dark:text-gray-300'"
                                        class="text-sm font-medium"
                                    ></span>
                                </button>
                            </div>

                            {{-- Send Button --}}
                            <x-filament::button
                                type="submit"
                                size="lg"
                                icon="heroicon-m-paper-airplane"
                                icon-position="after"
                                class="shadow-md"
                            >
                                Send
                            </x-filament::button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
