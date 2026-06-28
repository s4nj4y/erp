<x-public-layout>
    <h1 class="text-2xl font-bold mb-6 text-gray-900">Pesanan Saya</h1>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kode</th><th class="px-4 py-3 font-medium">UMKM</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Bayar</th><th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transaksi as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $t->kode_transaksi }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $t->umkm?->nama_umkm }}</td>
                            <td class="px-4 py-3 text-gray-500 tabular-nums whitespace-nowrap">{{ $t->tanggal->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3"><x-badge :status="$t->status" /></td>
                            <td class="px-4 py-3"><x-badge :status="$t->status_bayar" /></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('transaksi.show', $t) }}" class="text-indigo-600 hover:text-indigo-700 font-medium">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <x-icon name="inbox" class="w-9 h-9 mx-auto text-gray-300" />
                            <p class="mt-2">Belum ada pesanan.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $transaksi->links() }}</div>
    </div>
</x-public-layout>
