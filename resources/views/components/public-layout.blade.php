<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Informatics Business Center' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh flex flex-col bg-gray-50 text-gray-900 antialiased">
    <nav x-data="{ open: false }" class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ route('home') }}" class="font-extrabold text-xl tracking-tight text-indigo-600">IBC</a>

                {{-- Desktop --}}
                <div class="hidden md:flex items-center gap-6 text-sm">
                    @php $linkBase = 'text-gray-600 hover:text-indigo-600 transition-colors'; @endphp
                    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-indigo-600 font-medium' : $linkBase }}">Beranda</a>
                    <a href="{{ route('shop') }}" class="{{ request()->routeIs('shop') ? 'text-indigo-600 font-medium' : $linkBase }}">Shop</a>
                    @auth
                        @if (auth()->user()->isCustomer())
                            <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-1.5 {{ $linkBase }}">
                                <x-icon name="cart" class="w-5 h-5" /> Keranjang
                                @php $cartCount = auth()->user()->keranjang()->count(); @endphp
                                @if ($cartCount)
                                    <span class="ml-0.5 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-semibold text-white bg-indigo-600 rounded-full">{{ $cartCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('transaksi.index') }}" class="{{ $linkBase }}">Pesanan</a>
                        @else
                            <a href="{{ route('dashboard') }}" class="{{ $linkBase }}">Dashboard</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">@csrf
                            <button class="inline-flex items-center gap-1.5 {{ $linkBase }}"><x-icon name="logout" class="w-4 h-4" /> Keluar</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="{{ $linkBase }}">Masuk</a>
                        <a href="{{ route('register') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors">Daftar</a>
                    @endauth
                </div>

                {{-- Mobile toggle --}}
                <button type="button" @click="open = !open" class="md:hidden p-1.5 text-gray-600 hover:text-gray-900" aria-label="Menu" :aria-expanded="open">
                    <x-icon name="menu" x-show="!open" class="w-6 h-6" />
                    <x-icon name="close" x-show="open" class="w-6 h-6" x-cloak />
                </button>
            </div>
        </div>

        {{-- Mobile menu --}}
        <div x-show="open" x-transition x-cloak class="md:hidden border-t border-gray-200 bg-white">
            <div class="px-4 py-3 space-y-1 text-sm">
                @php $mLink = 'block rounded-lg px-3 py-2 text-gray-700 hover:bg-gray-100'; @endphp
                <a href="{{ route('home') }}" class="{{ $mLink }}">Beranda</a>
                <a href="{{ route('shop') }}" class="{{ $mLink }}">Shop</a>
                @auth
                    @if (auth()->user()->isCustomer())
                        <a href="{{ route('cart.index') }}" class="{{ $mLink }} flex items-center gap-2"><x-icon name="cart" class="w-5 h-5" /> Keranjang ({{ auth()->user()->keranjang()->count() }})</a>
                        <a href="{{ route('transaksi.index') }}" class="{{ $mLink }}">Pesanan</a>
                    @else
                        <a href="{{ route('dashboard') }}" class="{{ $mLink }}">Dashboard</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}">@csrf
                        <button class="{{ $mLink }} w-full text-left text-red-600">Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="{{ $mLink }}">Masuk</a>
                    <a href="{{ route('register') }}" class="{{ $mLink }} bg-indigo-600 text-white hover:bg-indigo-700">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <x-flash />
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 py-6 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Informatics Business Center
    </footer>
</body>
</html>
