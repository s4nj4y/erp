<x-public-layout>
    <a href="{{ route('shop') }}" class="text-sm text-indigo-600">&larr; Kembali ke Shop</a>

    <div class="grid md:grid-cols-2 gap-8 mt-4 bg-white p-6 rounded-lg border border-gray-200">
        <div class="aspect-square bg-gray-100 flex items-center justify-center text-gray-300 rounded">
            @if ($produk->gambar)
                <img src="{{ asset('storage/'.$produk->gambar) }}" alt="{{ $produk->nama_produk }}" class="object-cover w-full h-full rounded">
            @else
                <span>Tanpa gambar</span>
            @endif
        </div>
        <div>
            <h1 class="text-2xl font-bold">{{ $produk->nama_produk }}</h1>
            <div class="text-2xl text-indigo-600 font-semibold my-2">Rp{{ number_format($produk->harga, 0, ',', '.') }}</div>
            <div class="text-sm text-gray-500 mb-4">oleh {{ $produk->umkm?->nama_umkm }} &middot; Stok: {{ $produk->stok }}</div>
            <p class="text-gray-700 whitespace-pre-line">{{ $produk->deskripsi }}</p>

            @if ($produk->detail->isNotEmpty())
                <dl class="mt-4 text-sm grid grid-cols-2 gap-1">
                    @foreach ($produk->detail as $d)
                        <dt class="text-gray-400">{{ $d->atribut?->atribut_produk }}</dt>
                        <dd>{{ $d->value }}</dd>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>

    @if ($produkLain->isNotEmpty())
        <h2 class="text-lg font-semibold mt-10 mb-3">Produk lain dari UMKM ini</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($produkLain as $p)
                <a href="{{ route('produk.show', $p) }}" class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-md transition">
                    <div class="font-medium text-sm truncate">{{ $p->nama_produk }}</div>
                    <div class="text-indigo-600 font-semibold text-sm">Rp{{ number_format($p->harga, 0, ',', '.') }}</div>
                </a>
            @endforeach
        </div>
    @endif
</x-public-layout>
