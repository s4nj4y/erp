<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $title ?? 'Masuk' }} · Informatics Business Center</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-dvh flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-br from-gray-50 to-indigo-50">
            <a href="{{ route('home') }}" class="mb-6 text-center">
                <span class="text-2xl font-extrabold tracking-tight text-indigo-600">IBC</span>
                <span class="block text-xs text-gray-500 mt-0.5">Informatics Business Center</span>
            </a>

            <div class="w-full sm:max-w-md bg-white px-6 py-8 shadow-lg rounded-2xl border border-gray-100">
                {{ $slot }}
            </div>

            <a href="{{ route('home') }}" class="mt-6 text-sm text-gray-500 hover:text-indigo-600">&larr; Kembali ke beranda</a>
        </div>
    </body>
</html>
