<x-public-layout>
    <h1 class="text-2xl font-bold mb-6">Checkout — {{ $umkm->nama_umkm }}</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
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

        <div class="bg-white rounded-lg shadow-sm p-6 h-fit">
            <h2 class="font-semibold mb-3">Pembayaran</h2>
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
                    <button class="w-full bg-indigo-600 text-white rounded-md py-2 text-sm mt-2">Buat Pesanan</button>
                </form>
            @endif
            <a href="{{ route('cart.index') }}" class="block text-center text-sm text-gray-500 mt-3">Kembali ke keranjang</a>
        </div>
    </div>
</x-public-layout>
