<x-public-layout>
    <h1 class="text-2xl font-bold mb-6 text-gray-900">Keranjang Belanja</h1>

    @if ($items->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <x-icon name="cart" class="w-10 h-10 mx-auto text-gray-300" />
            <p class="mt-3 text-gray-500">Keranjang masih kosong.</p>
            <a href="{{ route('shop') }}" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Mulai belanja</a>
        </div>
    @else
        @foreach ($grouped as $umkmId => $cartItems)
            @php $umkm = $cartItems->first()->produk->umkm; $subtotal = $cartItems->sum(fn($i) => $i->produk->harga * $i->qty); @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 font-semibold text-gray-800 flex items-center gap-2">
                    <x-icon name="store" class="w-5 h-5 text-gray-400" /> {{ $umkm?->nama_umkm }}
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($cartItems as $i)
                                <tr>
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $i->produk->nama_produk }}</div>
                                        <div class="text-gray-400 text-xs tabular-nums">Rp{{ number_format($i->produk->harga, 0, ',', '.') }} · stok {{ $i->produk->stok }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="inline-flex items-center rounded-lg border border-gray-200 overflow-hidden">
                                            <form method="POST" action="{{ route('cart.update', $i) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="decrease"><button class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-gray-50" aria-label="Kurangi">−</button></form>
                                            <span class="w-10 text-center tabular-nums">{{ $i->qty }}</span>
                                            <form method="POST" action="{{ route('cart.update', $i) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="increase"><button class="w-9 h-9 flex items-center justify-center text-gray-600 hover:bg-gray-50" aria-label="Tambah">+</button></form>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium tabular-nums whitespace-nowrap">Rp{{ number_format($i->produk->harga * $i->qty, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <form method="POST" action="{{ route('cart.destroy', $i) }}" onsubmit="return confirm('Hapus item?')">@csrf @method('DELETE')<button class="inline-flex items-center justify-center w-9 h-9 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" aria-label="Hapus item"><x-icon name="close" class="w-4 h-4" /></button></form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 bg-gray-50 flex flex-wrap items-center justify-between gap-3">
                    <span class="font-semibold text-gray-800 tabular-nums">Subtotal: Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    <a href="{{ route('checkout.show', $umkm) }}" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">Checkout</a>
                </div>
            </div>
        @endforeach
    @endif
</x-public-layout>
