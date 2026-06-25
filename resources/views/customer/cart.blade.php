<x-public-layout>
    <h1 class="text-2xl font-bold mb-6">Keranjang Belanja</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    @if ($items->isEmpty())
        <div class="bg-white p-8 rounded-lg text-center text-gray-500">
            Keranjang kosong. <a href="{{ route('shop') }}" class="text-indigo-600">Mulai belanja</a>.
        </div>
    @else
        @foreach ($grouped as $umkmId => $cartItems)
            @php $umkm = $cartItems->first()->produk->umkm; $subtotal = $cartItems->sum(fn($i) => $i->produk->harga * $i->qty); @endphp
            <div class="bg-white rounded-lg shadow-sm mb-6 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100 font-semibold">{{ $umkm?->nama_umkm }}</div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($cartItems as $i)
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="font-medium">{{ $i->produk->nama_produk }}</div>
                                    <div class="text-gray-400 text-xs">Rp{{ number_format($i->produk->harga, 0, ',', '.') }} · stok {{ $i->produk->stok }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-1">
                                        <form method="POST" action="{{ route('cart.update', $i) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="decrease"><button class="px-2 border rounded">−</button></form>
                                        <span class="w-8 text-center">{{ $i->qty }}</span>
                                        <form method="POST" action="{{ route('cart.update', $i) }}">@csrf @method('PATCH')<input type="hidden" name="action" value="increase"><button class="px-2 border rounded">+</button></form>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right">Rp{{ number_format($i->produk->harga * $i->qty, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('cart.destroy', $i) }}" onsubmit="return confirm('Hapus item?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Hapus</button></form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 bg-gray-50 flex items-center justify-between">
                    <span class="font-semibold">Subtotal: Rp{{ number_format($subtotal, 0, ',', '.') }}</span>
                    <a href="{{ route('checkout.show', $umkm) }}" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm">Checkout</a>
                </div>
            </div>
        @endforeach
    @endif
</x-public-layout>
