<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin · {{ $title ?? 'IBC' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 antialiased">
<div class="min-h-screen flex">
    {{-- Sidebar --}}
    <aside class="w-60 bg-gray-900 text-gray-300 flex-shrink-0 hidden md:block">
        <div class="h-16 flex items-center px-6 text-white font-bold text-lg border-b border-gray-800">IBC Admin</div>
        @php
            $nav = [
                ['admin.dashboard', 'Dashboard', null],
                ['admin.umkm.index', 'UMKM', 'admin.umkm.*'],
                ['admin.produk.index', 'Produk', 'admin.produk.*'],
                ['admin.users.index', 'Users', 'admin.users.*'],
                ['admin.bank.index', 'Bank', 'admin.bank.*'],
                ['admin.jenis-usaha.index', 'Jenis Usaha', 'admin.jenis-usaha.*'],
                ['admin.jenis-pengeluaran.index', 'Jenis Pengeluaran', 'admin.jenis-pengeluaran.*'],
                ['admin.kategori-produk.index', 'Kategori Produk', 'admin.kategori-produk.*'],
            ];
        @endphp
        <nav class="py-4 text-sm">
            @foreach ($nav as [$route, $label, $pattern])
                @php $active = $pattern ? request()->routeIs($pattern) : request()->routeIs($route); @endphp
                <a href="{{ route($route) }}"
                   class="block px-6 py-2.5 hover:bg-gray-800 {{ $active ? 'bg-gray-800 text-white border-l-4 border-indigo-500' : '' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6">
            <h1 class="font-semibold text-gray-800">{{ $header ?? 'Master Data' }}</h1>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('home') }}" class="text-gray-500 hover:text-indigo-600">Lihat Situs</a>
                <span class="text-gray-400">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-red-600 hover:underline">Keluar</button>
                </form>
            </div>
        </header>

        <main class="p-6 flex-1">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
