<x-umkm-layout header="Laporan Pendapatan">
    @include('umkm.keuangan.laporan._filter', ['routeName' => 'umkm.laporan.pendapatan'])

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b">
            <h2 class="font-bold">{{ $title }}</h2>
            <p class="text-xs text-gray-500">{{ $umkm->nama_umkm }} · {{ $periode }}</p>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500"><tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Kode</th><th class="px-4 py-2">Pembeli</th><th class="px-4 py-2 text-right">Total</th></tr></thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($penjualan as $t)
                    @php $tot = $t->detail->sum(fn($d) => $d->harga * $d->qty); @endphp
                    <tr>
                        <td class="px-4 py-2">{{ $t->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-2">{{ $t->kode_transaksi }}</td>
                        <td class="px-4 py-2 text-gray-500">{{ $t->customer?->name }}</td>
                        <td class="px-4 py-2 text-right">Rp{{ number_format($tot, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">Tidak ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
            <tfoot><tr class="bg-gray-50 font-bold"><td class="px-4 py-3" colspan="3">Total Pendapatan</td><td class="px-4 py-3 text-right">Rp{{ number_format($totalPendapatan, 0, ',', '.') }}</td></tr></tfoot>
        </table>
    </div>
</x-umkm-layout>
