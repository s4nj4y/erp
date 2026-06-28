<x-umkm-layout header="Detail Pesanan">
    <a href="{{ route('umkm.transaksi.index') }}" class="inline-flex items-center gap-1 text-sm text-emerald-600 hover:text-emerald-700">&larr; Pesanan Masuk</a>

    <div class="grid md:grid-cols-3 gap-6 mt-4">
        <div class="md:col-span-2 card overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-100">
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

        <div class="card p-6 h-fit space-y-4">
            <div>
                <div class="text-xs text-gray-400 mb-1">Status Pesanan</div>
                <x-badge :status="$transaksi->status" />
            </div>
            <div>
                <div class="text-xs text-gray-400 mb-1">Status Pembayaran</div>
                <x-badge :status="$transaksi->status_bayar" />
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
                    <form method="POST" action="{{ route('umkm.transaksi.verifikasi', $transaksi) }}">@csrf<button class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">Verifikasi</button></form>
                    <form method="POST" action="{{ route('umkm.transaksi.tolak', $transaksi) }}" onsubmit="return confirm('Tolak pembayaran? Stok dikembalikan.')">@csrf<button class="w-full rounded-lg border border-red-300 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 transition-colors">Tolak</button></form>
                </div>
            @endif

            @if ($transaksi->status === 'diproses')
                <form method="POST" action="{{ route('umkm.transaksi.kirim', $transaksi) }}">@csrf<button class="w-full rounded-lg bg-emerald-600 py-2.5 text-sm font-semibold text-white hover:bg-emerald-700 transition-colors">Tandai Dikirim</button></form>
            @endif
        </div>
    </div>
</x-umkm-layout>
