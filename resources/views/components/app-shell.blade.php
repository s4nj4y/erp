@props([
    'brand' => 'IBC',
    'nav' => [],                 // baris: [route, label, pattern, icon]
    'title' => 'IBC',
    'header' => null,
    'sidebar' => 'bg-slate-900 text-slate-300',   // class lengkap (Tailwind-safe)
    'brandBorder' => 'border-slate-800',
    'linkHover' => 'hover:bg-white/5 hover:text-white',
    'linkActive' => 'bg-white/10 text-white',
    'activeBar' => 'border-indigo-400',
    'accent' => 'hover:text-indigo-600',
])

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900 antialiased">
<div x-data="{ open: false }" class="min-h-dvh md:flex">

    {{-- Backdrop (mobile) --}}
    <div x-show="open" x-transition.opacity @click="open = false"
         class="fixed inset-0 z-30 bg-black/50 md:hidden" x-cloak></div>

    {{-- Sidebar --}}
    <aside x-cloak
           :class="open ? 'translate-x-0' : '-translate-x-full'"
           class="fixed inset-y-0 left-0 z-40 w-64 transform transition-transform duration-200 ease-out
                  md:static md:translate-x-0 md:shrink-0 flex flex-col {{ $sidebar }}">
        <div class="h-16 flex items-center justify-between px-6 text-white font-bold text-lg border-b {{ $brandBorder }}">
            <span>{{ $brand }}</span>
            <button type="button" @click="open = false" class="md:hidden text-white/70 hover:text-white" aria-label="Tutup menu">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto py-4 text-sm" aria-label="Navigasi utama">
            @foreach ($nav as [$route, $label, $pattern, $icon])
                @php $active = $pattern ? request()->routeIs($pattern) : request()->routeIs($route); @endphp
                <a href="{{ route($route) }}" @if($active) aria-current="page" @endif
                   class="flex items-center gap-3 px-6 py-2.5 border-l-4 transition-colors
                          {{ $active ? $linkActive.' '.$activeBar : 'border-transparent '.$linkHover }}">
                    <x-icon :name="$icon" class="w-5 h-5" />
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    {{-- Konten --}}
    <div class="flex-1 flex flex-col min-w-0">
        <header class="sticky top-0 z-20 h-16 bg-white/90 backdrop-blur border-b border-gray-200 flex items-center justify-between gap-3 px-4 sm:px-6">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button" @click="open = true" class="md:hidden -ml-1 p-1.5 text-gray-600 hover:text-gray-900" aria-label="Buka menu">
                    <x-icon name="menu" class="w-6 h-6" />
                </button>
                <h1 class="font-semibold text-gray-800 truncate">{{ $header ?? $title }}</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-4 text-sm">
                <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center gap-1.5 text-gray-500 {{ $accent }}">
                    <x-icon name="external" class="w-4 h-4" /> Lihat Situs
                </a>
                <span class="hidden sm:inline text-gray-400">{{ auth()->user()->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="inline-flex items-center gap-1.5 text-red-600 hover:text-red-700" aria-label="Keluar">
                        <x-icon name="logout" class="w-4 h-4" /> <span class="hidden sm:inline">Keluar</span>
                    </button>
                </form>
            </div>
        </header>

        <main class="p-4 sm:p-6 flex-1">
            <x-flash />
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
