<x-public-layout>
    <h1 class="text-2xl font-bold mb-4 text-gray-900">Shop</h1>

    <form method="GET" action="{{ route('shop') }}" class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-gray-400">
                    <x-icon name="dot" class="w-4 h-4" />
                </span>
                <label for="q" class="sr-only">Cari produk</label>
                <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk..."
                       class="w-full rounded-lg border-gray-300 pl-9 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>
            <div class="sm:w-56">
                <label for="kategori" class="sr-only">Kategori</label>
                <select id="kategori" name="kategori" class="w-full rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Semua kategori</option>
                    @foreach ($kategori as $k)
                        <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Filter</button>
        </div>
    </form>

    @if ($produk->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <x-icon name="cube" class="w-10 h-10 mx-auto text-gray-300" />
            <p class="mt-3 text-gray-500">Tidak ada produk yang cocok.</p>
            <a href="{{ route('shop') }}" class="mt-3 inline-block text-sm text-indigo-600 hover:text-indigo-700">Reset filter</a>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($produk as $p)
                <x-produk-card :produk="$p" />
            @endforeach
        </div>
        <div class="mt-8">{{ $produk->links() }}</div>
    @endif
</x-public-layout>
