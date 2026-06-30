<x-public-layout>
    <a href="{{ route('shop') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700">&larr; Kembali ke Shop</a>

    <div class="grid md:grid-cols-2 gap-8 mt-4 bg-white p-6 rounded-2xl border border-gray-200">
        <div class="aspect-square bg-gray-100 flex items-center justify-center text-gray-300 rounded-xl overflow-hidden">
            @if ($produk->gambar_url)
                <img src="{{ $produk->gambar_url }}" alt="{{ $produk->nama_produk }}" class="object-cover w-full h-full">
            @else
                <x-icon name="cube" class="w-16 h-16" />
            @endif
        </div>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $produk->nama_produk }}</h1>
            <div class="text-3xl text-indigo-600 font-bold tabular-nums my-3">Rp{{ number_format($produk->harga, 0, ',', '.') }}</div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-5">
                <span>oleh {{ $produk->umkm?->nama_umkm }}</span>
                <span class="text-gray-300">&middot;</span>
                @if ($produk->stok > 0)
                    <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-200">Stok {{ $produk->stok }}</span>
                @else
                    <span class="inline-flex items-center rounded-full bg-red-50 px-2 py-0.5 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-200">Stok habis</span>
                @endif
            </div>

            @auth
                @if (auth()->user()->isCustomer())
                    @if ($produk->stok > 0 && $produk->show)
                        <form method="POST" action="{{ route('cart.store', $produk) }}" class="flex items-center gap-3 mb-5">
                            @csrf
                            <label for="qty" class="sr-only">Jumlah</label>
                            <input id="qty" type="number" name="qty" value="1" min="1" max="{{ $produk->stok }}"
                                   class="w-20 rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <button class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors">
                                <x-icon name="cart" class="w-4 h-4" /> Tambah ke Keranjang
                            </button>
                        </form>
                    @else
                        <p class="text-red-500 text-sm mb-5">Stok habis.</p>
                    @endif
                @endif
            @else
                <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors mb-5">
                    Masuk untuk membeli
                </a>
            @endauth

            @if ($produk->deskripsi)
                <p class="text-gray-700 whitespace-pre-line leading-relaxed">{{ $produk->deskripsi }}</p>
            @endif

            @if ($produk->detail->isNotEmpty())
                <dl class="mt-5 text-sm grid grid-cols-2 gap-y-1.5 border-t border-gray-100 pt-4">
                    @foreach ($produk->detail as $d)
                        <dt class="text-gray-400">{{ $d->atribut?->atribut_produk }}</dt>
                        <dd class="text-gray-800">{{ $d->value }}</dd>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>

    @if ($produkLain->isNotEmpty())
        <h2 class="text-lg font-semibold mt-10 mb-4 text-gray-900">Produk lain dari UMKM ini</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($produkLain as $p)
                <x-produk-card :produk="$p" />
            @endforeach
        </div>
    @endif
</x-public-layout>
