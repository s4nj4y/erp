<x-umkm-layout header="Detail Pengeluaran">
    <a href="{{ route('umkm.pengeluaran.index') }}" class="text-sm text-emerald-600">&larr; Pengeluaran</a>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm mt-4 overflow-hidden">
        <div class="px-4 py-3 border-b">
            <div class="font-semibold">{{ $pengeluaran->jenis?->nama ?? 'Pengeluaran' }}</div>
            <div class="text-xs text-gray-400">{{ $pengeluaran->tanggal_pengeluaran->format('d/m/Y') }}</div>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500"><tr><th class="px-4 py-2">Keterangan</th><th class="px-4 py-2 text-center">Qty</th><th class="px-4 py-2 text-right">Harga</th><th class="px-4 py-2 text-right">Subtotal</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($pengeluaran->detail as $d)
                    <tr><td class="px-4 py-2">{{ $d->keterangan }}</td><td class="px-4 py-2 text-center">{{ $d->qty }}</td><td class="px-4 py-2 text-right">Rp{{ number_format($d->harga, 0, ',', '.') }}</td><td class="px-4 py-2 text-right">Rp{{ number_format($d->sub_total, 0, ',', '.') }}</td></tr>
                @endforeach
            </tbody>
            <tfoot><tr class="bg-gray-50 font-semibold"><td class="px-4 py-2" colspan="3">Total</td><td class="px-4 py-2 text-right">Rp{{ number_format($pengeluaran->total_harga, 0, ',', '.') }}</td></tr></tfoot>
        </table>
    </div>
</x-umkm-layout>
