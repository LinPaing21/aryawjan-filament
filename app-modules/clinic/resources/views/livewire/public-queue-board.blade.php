<div wire:poll.10s class="min-h-screen p-6 space-y-6">
    {{-- Header bar --}}
    <div class="flex items-center justify-between rounded-xl bg-gray-900 px-6 py-4">
        <div class="flex items-center gap-3">
            <x-heroicon-s-queue-list class="h-7 w-7 text-amber-400" />
            <span class="text-xl font-bold tracking-wide text-white">Live Queue Board</span>
        </div>
        <div class="text-right">
            <p class="text-sm text-gray-400">{{ now()->format('l, F j, Y') }}</p>
            <p class="text-2xl font-mono font-bold text-amber-400">{{ now()->format('H:i') }}</p>
        </div>
    </div>

    @php
        $queues = $this->getActiveDoctorQueues();
    @endphp

    @if(empty($queues))
        <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-gray-700 bg-gray-900 py-24">
            <x-heroicon-o-calendar class="mb-4 h-16 w-16 text-gray-600" />
            <p class="text-lg font-semibold text-gray-400">No doctors are scheduled right now</p>
            <p class="mt-1 text-sm text-gray-600">This board updates automatically every 10 seconds</p>
        </div>
    @else
        <div @class([
            'grid gap-6',
            'grid-cols-1' => count($queues) === 1,
            'grid-cols-2' => count($queues) === 2,
            'grid-cols-3' => count($queues) >= 3,
        ])>
            @foreach($queues as $queue)
                @php
                    $isNextReQueued = $queue['next'] && $queue['next']->status->value === 're_queued';
                    $isCurrentReQueued = $queue['current'] && $queue['current']->status->value === 're_queued';
                @endphp
                <div class="overflow-hidden rounded-2xl border border-gray-700 bg-gray-800 shadow-md">
                    {{-- Doctor header --}}
                    <div class="bg-gray-900 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white">
                                <x-heroicon-s-user class="h-5 w-5" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">Doctor</p>
                                <p class="text-lg font-bold text-white">{{ $queue['doctor']->name }}</p>
                            </div>
                            <div class="ml-auto text-right">
                                <p class="text-xs font-semibold uppercase tracking-widest text-gray-400">In Queue</p>
                                <p class="text-2xl font-bold text-amber-400">{{ $queue['remaining'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Token display --}}
                    <div class="grid grid-cols-2 divide-x divide-gray-700">
                        {{-- Current token --}}
                        <div class="flex flex-col items-center justify-center px-6 py-10">
                            <p class="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">Now Serving</p>
                            @if($queue['current'])
                                <div @class([
                                    'flex h-28 w-28 items-center justify-center rounded-full shadow-lg ring-4',
                                    'bg-cyan-500 ring-cyan-900' => $isCurrentReQueued,
                                    'bg-amber-500 ring-amber-900' => !$isCurrentReQueued,
                                ])>
                                    <span class="text-3xl font-extrabold text-white">
                                        {{ last(explode('-', $queue['current']->token_number)) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">{{ $queue['current']->token_number }}</p>
                                @if($isCurrentReQueued)
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-cyan-900/40 px-2.5 py-0.5 text-xs font-semibold text-cyan-300">
                                        <x-heroicon-m-arrow-path class="h-3 w-3" />
                                        Re-queued
                                    </span>
                                @endif
                            @else
                                <div class="flex h-28 w-28 items-center justify-center rounded-full border-4 border-dashed border-gray-700">
                                    <span class="text-3xl font-extrabold text-gray-600">—</span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">No active token</p>
                            @endif
                        </div>

                        {{-- Next token --}}
                        <div class="flex flex-col items-center justify-center px-6 py-10">
                            <p class="mb-3 text-xs font-bold uppercase tracking-widest text-gray-500">Up Next</p>
                            @if($queue['next'])
                                <div @class([
                                    'flex h-20 w-20 items-center justify-center rounded-full shadow-sm ring-2',
                                    'bg-cyan-900/30 ring-cyan-700' => $isNextReQueued,
                                    'bg-gray-700 ring-gray-600' => !$isNextReQueued,
                                ])>
                                    <span @class([
                                        'text-2xl font-extrabold',
                                        'text-cyan-300' => $isNextReQueued,
                                        'text-gray-200' => !$isNextReQueued,
                                    ])>
                                        {{ last(explode('-', $queue['next']->token_number)) }}
                                    </span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">{{ $queue['next']->token_number }}</p>
                                @if($isNextReQueued)
                                    <span class="mt-2 inline-flex items-center gap-1 rounded-full bg-cyan-900/40 px-2.5 py-0.5 text-xs font-semibold text-cyan-300">
                                        <x-heroicon-m-arrow-path class="h-3 w-3" />
                                        Re-queued
                                    </span>
                                @endif
                            @else
                                <div class="flex h-20 w-20 items-center justify-center rounded-full border-4 border-dashed border-gray-700">
                                    <span class="text-2xl font-extrabold text-gray-600">—</span>
                                </div>
                                <p class="mt-3 text-xs text-gray-500">No next token</p>
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-gray-700 bg-gray-900 px-6 py-3">
                        <p class="text-center text-xs text-gray-600">
                            Auto-refreshes every 10 seconds &nbsp;·&nbsp; {{ now()->format('H:i:s') }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
