<x-public-layout>
    <h1 class="text-2xl font-bold mb-4">Shop</h1>

    <form method="GET" action="{{ route('shop') }}" class="flex flex-wrap gap-2 mb-6">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari produk..."
               class="rounded-md border-gray-300 text-sm">
        <select name="kategori" class="rounded-md border-gray-300 text-sm">
            <option value="">Semua kategori</option>
            @foreach ($kategori as $k)
                <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">Filter</button>
    </form>

    @if ($produk->isEmpty())
        <p class="text-gray-500">Tidak ada produk yang cocok.</p>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($produk as $p)
                <a href="{{ route('produk.show', $p) }}" class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition">
                    <div class="aspect-square bg-gray-100 flex items-center justify-center text-gray-300">
                        @if ($p->gambar)
                            <img src="{{ asset('storage/'.$p->gambar) }}" alt="{{ $p->nama_produk }}" class="object-cover w-full h-full">
                        @else
                            <span class="text-xs">Tanpa gambar</span>
                        @endif
                    </div>
                    <div class="p-3">
                        <div class="font-medium text-sm truncate">{{ $p->nama_produk }}</div>
                        <div class="text-indigo-600 font-semibold text-sm">Rp{{ number_format($p->harga, 0, ',', '.') }}</div>
                        <div class="text-xs text-gray-400 truncate">{{ $p->umkm?->nama_umkm }}</div>
                    </div>
                </a>
            @endforeach
        </div>
        <div class="mt-6">{{ $produk->links() }}</div>
    @endif
</x-public-layout>
