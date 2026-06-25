<x-umkm-layout header="Detail Pesanan">
    <a href="{{ route('umkm.transaksi.index') }}" class="text-sm text-emerald-600">&larr; Pesanan Masuk</a>

    <div class="grid md:grid-cols-3 gap-6 mt-4">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b">
                <div class="font-bold">{{ $transaksi->kode_transaksi }}</div>
                <div class="text-xs text-gray-400">{{ $transaksi->tanggal->format('d/m/Y H:i') }} · {{ $transaksi->customer?->name }} ({{ $transaksi->customer?->email }})</div>
            </div>
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transaksi->detail as $d)
                        <tr>
                            <td class="px-4 py-3">{{ $d->produk?->nama_produk }}</td>
                            <td class="px-4 py-3 text-center">{{ $d->qty }} × Rp{{ number_format($d->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">Rp{{ number_format($d->harga * $d->qty, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot><tr class="bg-gray-50 font-semibold"><td class="px-4 py-3" colspan="2">Total</td><td class="px-4 py-3 text-right">Rp{{ number_format($total, 0, ',', '.') }}</td></tr></tfoot>
            </table>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6 h-fit space-y-4">
            <div>
                <div class="text-xs text-gray-400">Status Pesanan</div>
                <div class="font-medium">{{ ucfirst($transaksi->status) }}</div>
            </div>
            <div>
                <div class="text-xs text-gray-400">Status Pembayaran</div>
                <div class="font-medium">{{ ucfirst(str_replace('_', ' ', $transaksi->status_bayar)) }}</div>
            </div>

            <div>
                <div class="text-xs text-gray-400 mb-1">Bukti Pembayaran</div>
                @if ($transaksi->bukti_pembayaran)
                    <a href="{{ asset('storage/'.$transaksi->bukti_pembayaran) }}" target="_blank">
                        <img src="{{ asset('storage/'.$transaksi->bukti_pembayaran) }}" class="w-full rounded border">
                    </a>
                @else
                    <p class="text-sm text-gray-400">Belum ada bukti.</p>
                @endif
            </div>

            @if ($transaksi->status_bayar === 'menunggu_verifikasi')
                <div class="grid grid-cols-2 gap-2">
                    <form method="POST" action="{{ route('umkm.transaksi.verifikasi', $transaksi) }}">@csrf<button class="w-full bg-emerald-600 text-white rounded-md py-2 text-sm">Verifikasi</button></form>
                    <form method="POST" action="{{ route('umkm.transaksi.tolak', $transaksi) }}" onsubmit="return confirm('Tolak pembayaran? Stok dikembalikan.')">@csrf<button class="w-full border border-red-500 text-red-600 rounded-md py-2 text-sm">Tolak</button></form>
                </div>
            @endif

            @if ($transaksi->status === 'diproses')
                <form method="POST" action="{{ route('umkm.transaksi.kirim', $transaksi) }}">@csrf<button class="w-full bg-emerald-600 text-white rounded-md py-2 text-sm">Tandai Dikirim</button></form>
            @endif
        </div>
    </div>
</x-umkm-layout>
