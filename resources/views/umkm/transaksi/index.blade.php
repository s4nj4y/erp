<x-umkm-layout header="Pesanan Masuk">
    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <label for="status" class="sr-only">Status bayar</label>
        <select id="status" name="status" class="rounded-lg border-gray-300 text-sm focus:border-emerald-500 focus:ring-emerald-500">
            <option value="">Semua status bayar</option>
            @foreach (['belum' => 'Belum bayar', 'menunggu_verifikasi' => 'Menunggu verifikasi', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $k => $v)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
    </form>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Kode</th><th class="px-4 py-3 font-medium">Pembeli</th>
                        <th class="px-4 py-3 font-medium">Tanggal</th><th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium">Bayar</th><th class="px-4 py-3 font-medium text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($transaksi as $t)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $t->kode_transaksi }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $t->customer?->name }}</td>
                            <td class="px-4 py-3 text-gray-500 tabular-nums whitespace-nowrap">{{ $t->tanggal->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3"><x-badge :status="$t->status" /></td>
                            <td class="px-4 py-3"><x-badge :status="$t->status_bayar" /></td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('umkm.transaksi.show', $t) }}" class="font-medium text-emerald-600 hover:text-emerald-700">Detail</a></td>
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
</x-umkm-layout>
