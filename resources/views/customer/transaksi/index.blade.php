<x-public-layout>
    <h1 class="text-2xl font-bold mb-6">Pesanan Saya</h1>

    @if (session('success'))
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Kode</th><th class="px-4 py-3">UMKM</th>
                    <th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Bayar</th><th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($transaksi as $t)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $t->kode_transaksi }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->umkm?->nama_umkm }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->tanggal->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">{{ ucfirst($t->status) }}</td>
                        <td class="px-4 py-3">{{ ucfirst(str_replace('_', ' ', $t->status_bayar)) }}</td>
                        <td class="px-4 py-3 text-right"><a href="{{ route('transaksi.show', $t) }}" class="text-indigo-600 hover:underline">Detail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada pesanan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $transaksi->links() }}</div>
    </div>
</x-public-layout>
