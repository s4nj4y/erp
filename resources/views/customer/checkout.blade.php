<x-public-layout>
    <h1 class="text-2xl font-bold mb-6">Checkout — {{ $umkm->nama_umkm }}</h1>

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr><th class="px-4 py-3">Produk</th><th class="px-4 py-3 text-center">Qty</th><th class="px-4 py-3 text-right">Subtotal</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($items as $i)
                        <tr>
                            <td class="px-4 py-3">{{ $i->produk->nama_produk }}</td>
                            <td class="px-4 py-3 text-center">{{ $i->qty }}</td>
                            <td class="px-4 py-3 text-right">Rp{{ number_format($i->produk->harga * $i->qty, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-semibold"><td class="px-4 py-3" colspan="2">Total</td><td class="px-4 py-3 text-right">Rp{{ number_format($total, 0, ',', '.') }}</td></tr>
                </tfoot>
            </table>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 h-fit">
            <h2 class="font-semibold mb-3 text-gray-800">Pembayaran</h2>
            @if ($umkm->rekening->isEmpty())
                <p class="text-sm text-red-500">UMKM belum menambahkan rekening bank. Checkout belum bisa dilakukan.</p>
            @else
                <form method="POST" action="{{ route('checkout.store', $umkm) }}">
                    @csrf
                    <label class="block text-sm font-medium mb-1">Transfer ke Bank</label>
                    <select name="bank_id" required class="w-full rounded-md border-gray-300 text-sm mb-2">
                        @foreach ($umkm->rekening as $rek)
                            <option value="{{ $rek->bank_id }}">{{ $rek->bank?->nama_bank }} — {{ $rek->rekening }} (a.n {{ $rek->atas_nama }})</option>
                        @endforeach
                    </select>
                    <button class="w-full rounded-lg bg-indigo-600 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 transition-colors mt-2">Buat Pesanan</button>
                </form>
            @endif
            <a href="{{ route('cart.index') }}" class="block text-center text-sm text-gray-500 mt-3">Kembali ke keranjang</a>
        </div>
    </div>
</x-public-layout>
