<x-public-layout>
    {{-- Hero --}}
    <section class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-600 to-violet-700 px-6 py-12 sm:px-12 sm:py-16 mb-10 text-white">
        <div class="max-w-2xl">
            <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight">Jelajahi UMKM Binaan IBC</h1>
            <p class="mt-3 text-indigo-100">Kenali toko-toko UMKM binaan Informatics Business Center dan temukan produk langsung dari pelaku usaha.</p>
            <a href="{{ route('shop') }}"
               class="mt-6 inline-flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-50 transition-colors">
                Lihat Semua Produk <x-icon name="cart" class="w-4 h-4" />
            </a>
        </div>
    </section>

    <div class="flex items-end justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Daftar UMKM</h2>
        <a href="{{ route('shop') }}" class="text-sm text-indigo-600 hover:text-indigo-700">Lihat produk &rarr;</a>
    </div>

    {{-- Pencarian toko --}}
    <form method="GET" action="{{ route('home') }}" class="mb-6">
        <div class="flex gap-3">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <x-icon name="store" class="w-4 h-4" />
                </span>
                <label for="q" class="sr-only">Cari toko</label>
                <input id="q" type="search" name="q" value="{{ request('q') }}" placeholder="Cari toko, jenis usaha, atau deskripsi..."
                       class="w-full rounded-lg border-gray-300 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Cari</button>
        </div>
    </form>

    @if ($umkm->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <x-icon name="store" class="w-10 h-10 mx-auto text-gray-300" />
            @if (request('q'))
                <p class="mt-3 text-gray-500">Tidak ada toko yang cocok dengan "{{ request('q') }}".</p>
                <a href="{{ route('home') }}" class="mt-3 inline-block text-sm text-indigo-600 hover:text-indigo-700">Reset pencarian</a>
            @else
                <p class="mt-3 text-gray-500">Belum ada UMKM terdaftar.</p>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($umkm as $u)
                <x-umkm-card :umkm="$u" />
            @endforeach
        </div>
        <div class="mt-8">{{ $umkm->links() }}</div>
    @endif
</x-public-layout>
