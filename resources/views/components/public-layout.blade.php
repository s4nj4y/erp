<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Informatics Business Center' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 antialiased">
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="{{ route('home') }}" class="font-bold text-lg text-indigo-600">IBC</a>
            <div class="space-x-4 text-sm">
                <a href="{{ route('home') }}" class="hover:text-indigo-600">Beranda</a>
                <a href="{{ route('shop') }}" class="hover:text-indigo-600">Shop</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="hover:text-indigo-600">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="hover:text-indigo-600">Masuk</a>
                    <a href="{{ route('register') }}" class="px-3 py-1.5 bg-indigo-600 text-white rounded-md">Daftar</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <footer class="border-t border-gray-200 mt-12 py-6 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} Informatics Business Center
    </footer>
</body>
</html>
