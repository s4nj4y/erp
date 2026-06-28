<x-public-layout>
    <a href="{{ route('transaksi.index') }}" class="text-sm text-indigo-600">&larr; Pesanan Saya</a>

    <div class="grid md:grid-cols-3 gap-6 mt-4">
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
            <div class="px-4 py-3 border-b flex items-center justify-between">
                <div>
                    <div class="font-bold">{{ $transaksi->kode_transaksi }}</div>
                    <div class="text-xs text-gray-400">{{ $transaksi->tanggal->format('d/m/Y H:i') }} · {{ $transaksi->umkm?->nama_umkm }}</div>
                </div>
                <a href="{{ route('transaksi.invoice', $transaksi) }}" class="text-sm text-indigo-600">Lihat Invoice</a>
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
                <div class="text-xs text-gray-400 mb-1">Status Pesanan</div>
                <x-badge :status="$transaksi->status" />
            </div>
            <div>
                <div class="text-xs text-gray-400 mb-1">Status Pembayaran</div>
                <x-badge :status="$transaksi->status_bayar" />
            </div>

            @php $rek = $transaksi->umkm?->rekening->firstWhere('bank_id', $transaksi->bank_id); @endphp
            @if ($rek)
                <div class="text-sm bg-gray-50 rounded p-3">
                    Transfer ke <strong>{{ $rek->bank?->nama_bank }}</strong><br>
                    {{ $rek->rekening }}<br>a.n {{ $rek->atas_nama }}
                </div>
            @endif

            <div>
                <div class="text-xs text-gray-400 mb-1">Bukti Pembayaran</div>
                @if ($transaksi->bukti_pembayaran)
                    <img src="{{ asset('storage/'.$transaksi->bukti_pembayaran) }}" class="w-full rounded border mb-2">
                @endif
                @if ($transaksi->status !== 'selesai')
                    <form method="POST" action="{{ route('transaksi.bukti', $transaksi) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="bukti_pembayaran" accept="image/*" required class="text-sm mb-2 w-full">
                        <button class="w-full bg-indigo-600 text-white rounded-md py-2 text-sm">Unggah Bukti</button>
                    </form>
                @endif
            </div>

            @if ($transaksi->status !== 'selesai')
                <form method="POST" action="{{ route('transaksi.terima', $transaksi) }}" onsubmit="return confirm('Tandai pesanan diterima?')">
                    @csrf
                    <button class="w-full border border-green-500 text-green-600 rounded-md py-2 text-sm">Pesanan Diterima</button>
                </form>
            @endif
        </div>
    </div>
</x-public-layout>
