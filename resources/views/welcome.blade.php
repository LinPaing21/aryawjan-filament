<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} — Clinic AI</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-950 flex flex-col items-center justify-center font-sans antialiased">

    <div class="w-full max-w-md px-6 text-center">

        {{-- Icon --}}
        <div class="mx-auto mb-6 flex items-center justify-center w-16 h-16 rounded-2xl bg-primary-600 shadow-lg">
            <svg class="w-9 h-9 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
        </div>

        {{-- Heading --}}
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
            {{ config('app.name') }}
        </h1>
        <p class="mt-2 text-base text-gray-500 dark:text-gray-400">
            AI-powered clinic management for doctors and pharmacists.
        </p>

        {{-- CTA --}}
        <div class="mt-8 flex flex-col gap-3">
            <a
                href="/admin"
                class="inline-flex items-center justify-center gap-2 w-full px-5 py-3 rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-semibold text-sm shadow-md transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                </svg>
                Go to Dashboard
            </a>
            <a
                href="/admin/login"
                class="inline-flex items-center justify-center w-full px-5 py-3 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-medium text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
            >
                Sign in
            </a>
        </div>

        {{-- Footer --}}
        <p class="mt-10 text-xs text-gray-400 dark:text-gray-600">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

</body>
</html>
