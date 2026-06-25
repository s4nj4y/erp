<x-public-layout>
    <h1 class="text-2xl font-bold mb-1">Produk UMKM Lokal</h1>
    <p class="text-gray-500 mb-6">Temukan produk dari UMKM binaan Informatics Business Center.</p>

    @if ($produk->isEmpty())
        <p class="text-gray-500">Belum ada produk.</p>
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
