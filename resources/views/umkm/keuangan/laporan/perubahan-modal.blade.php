<x-umkm-layout header="Laporan Perubahan Modal">
    @include('umkm.keuangan.laporan._filter', ['routeName' => 'umkm.laporan.perubahan-modal'])

    <div class="max-w-2xl bg-white rounded-lg shadow-sm p-6">
        <h2 class="font-bold text-lg">{{ $title }}</h2>
        <p class="text-sm text-gray-500 mb-4">{{ $umkm->nama_umkm }} · {{ $periode }}</p>

        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100">
                <tr><td class="py-2">Modal Awal</td><td class="py-2 text-right">Rp{{ number_format($modalAwal, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2">Penambahan Modal</td><td class="py-2 text-right text-green-600">+Rp{{ number_format($penambahan, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2">Laba Bersih Periode</td><td class="py-2 text-right {{ $labaBersih >= 0 ? 'text-green-600' : 'text-red-500' }}">{{ $labaBersih >= 0 ? '+' : '-' }}Rp{{ number_format(abs($labaBersih), 0, ',', '.') }}</td></tr>
                <tr><td class="py-2">Pengambilan Modal</td><td class="py-2 text-right text-red-500">(Rp{{ number_format($pengambilan, 0, ',', '.') }})</td></tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 font-bold text-emerald-600"><td class="py-3">Modal Akhir</td><td class="py-3 text-right">Rp{{ number_format($modalAkhir, 0, ',', '.') }}</td></tr>
            </tfoot>
        </table>
    </div>
</x-umkm-layout>
