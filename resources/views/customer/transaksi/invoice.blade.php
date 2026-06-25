<x-public-layout>
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-sm p-8">
        <div class="flex justify-between items-start border-b pb-4 mb-4">
            <div>
                <h1 class="text-xl font-bold">INVOICE</h1>
                <div class="text-sm text-gray-500">{{ $transaksi->kode_transaksi }}</div>
            </div>
            <div class="text-right text-sm">
                <div class="font-semibold">{{ $transaksi->umkm?->nama_umkm }}</div>
                <div class="text-gray-500">{{ $transaksi->tanggal->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <div class="text-sm mb-4">
            <div class="text-gray-400">Pembeli</div>
            <div>{{ $transaksi->customer?->name }} — {{ $transaksi->customer?->email }}</div>
        </div>

        <table class="w-full text-sm mb-4">
            <thead class="border-b text-left text-gray-500">
                <tr><th class="py-2">Produk</th><th class="py-2 text-center">Qty</th><th class="py-2 text-right">Harga</th><th class="py-2 text-right">Subtotal</th></tr>
            </thead>
            <tbody>
                @foreach ($transaksi->detail as $d)
                    <tr class="border-b border-gray-50">
                        <td class="py-2">{{ $d->produk?->nama_produk }}</td>
                        <td class="py-2 text-center">{{ $d->qty }}</td>
                        <td class="py-2 text-right">Rp{{ number_format($d->harga, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">Rp{{ number_format($d->harga * $d->qty, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-bold"><td class="py-2" colspan="3">TOTAL</td><td class="py-2 text-right">Rp{{ number_format($total, 0, ',', '.') }}</td></tr>
            </tfoot>
        </table>

        <div class="text-sm text-gray-500">Status: {{ ucfirst($transaksi->status) }} · Pembayaran: {{ ucfirst(str_replace('_', ' ', $transaksi->status_bayar)) }}</div>

        <div class="mt-6 text-center print:hidden">
            <button onclick="window.print()" class="px-5 py-2 bg-indigo-600 text-white rounded-md text-sm">Cetak</button>
            <a href="{{ route('transaksi.show', $transaksi) }}" class="px-5 py-2 text-sm text-gray-500">Kembali</a>
        </div>
    </div>
</x-public-layout>
