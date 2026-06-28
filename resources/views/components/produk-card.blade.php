@props(['produk'])

<a href="{{ route('produk.show', $produk) }}"
   class="group flex flex-col bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all">
    <div class="aspect-square bg-gray-100 overflow-hidden flex items-center justify-center text-gray-300">
        @if ($produk->gambar)
            <img src="{{ asset('storage/'.$produk->gambar) }}" alt="{{ $produk->nama_produk }}" loading="lazy"
                 class="object-cover w-full h-full motion-safe:group-hover:scale-105 transition-transform duration-300">
        @else
            <x-icon name="cube" class="w-10 h-10" />
        @endif
    </div>
    <div class="p-3">
        <div class="font-medium text-sm text-gray-900 truncate">{{ $produk->nama_produk }}</div>
        <div class="text-indigo-600 font-semibold text-sm tabular-nums mt-0.5">Rp{{ number_format($produk->harga, 0, ',', '.') }}</div>
        <div class="text-xs text-gray-400 truncate mt-0.5">{{ $produk->umkm?->nama_umkm }}</div>
    </div>
</a>
