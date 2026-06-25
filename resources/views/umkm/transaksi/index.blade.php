<x-umkm-layout header="Pesanan Masuk">
    <form method="GET" class="flex gap-2 mb-4">
        <select name="status" class="rounded-md border-gray-300 text-sm">
            <option value="">Semua status bayar</option>
            @foreach (['belum' => 'Belum bayar', 'menunggu_verifikasi' => 'Menunggu verifikasi', 'terverifikasi' => 'Terverifikasi', 'ditolak' => 'Ditolak'] as $k => $v)
                <option value="{{ $k }}" @selected(request('status') === $k)>{{ $v }}</option>
            @endforeach
        </select>
        <button class="px-4 py-2 bg-gray-200 rounded-md text-sm">Filter</button>
    </form>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kode</th><th class="px-4 py-3">Pembeli</th>
                    <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Bayar</th><th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transaksi as $t)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $t->kode_transaksi }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->customer?->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->tanggal->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($t->status) }}</td>
                        <td class="px-4 py-3">
                            @php $badge = ['menunggu_verifikasi' => 'bg-yellow-100 text-yellow-700', 'terverifikasi' => 'bg-green-100 text-green-700', 'ditolak' => 'bg-red-100 text-red-700']; @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs {{ $badge[$t->status_bayar] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst(str_replace('_', ' ', $t->status_bayar)) }}</span>
                        </td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('umkm.transaksi.show', $t) }}" class="text-emerald-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $transaksi->links() }}</div>
    </div>
</x-umkm-layout>
