<x-umkm-layout header="Laporan Laba Rugi">
    @include('umkm.keuangan.laporan._filter', ['routeName' => 'umkm.laporan.laba-rugi'])

    <div class="max-w-2xl bg-white rounded-lg shadow-sm p-6">
        <h2 class="font-bold text-lg">{{ $title }}</h2>
        <p class="text-sm text-gray-500 mb-4">{{ $umkm->nama_umkm }} · {{ $periode }}</p>

        <table class="w-full text-sm">
            <tbody class="divide-y divide-gray-100">
                <tr><td class="py-2">Pendapatan Penjualan</td><td class="py-2 text-right">Rp{{ number_format($pendapatan, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2">Harga Pokok Penjualan (HPP)</td><td class="py-2 text-right text-red-500">(Rp{{ number_format($hpp, 0, ',', '.') }})</td></tr>
                <tr class="font-medium"><td class="py-2">Laba Kotor</td><td class="py-2 text-right">Rp{{ number_format($labaKotor, 0, ',', '.') }}</td></tr>
                <tr><td class="py-2">Beban / Pengeluaran</td><td class="py-2 text-right text-red-500">(Rp{{ number_format($pengeluaran, 0, ',', '.') }})</td></tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 font-bold {{ $labaBersih >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    <td class="py-3">Laba Bersih</td><td class="py-3 text-right">Rp{{ number_format($labaBersih, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</x-umkm-layout>
