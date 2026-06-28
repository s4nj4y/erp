<x-umkm-layout header="Pengeluaran">
    <div class="flex items-center justify-between mb-4">
        <div class="text-sm text-gray-500">Total pengeluaran: <span class="font-semibold text-gray-800">Rp{{ number_format($total, 0, ',', '.') }}</span></div>
        <a href="{{ route('umkm.pengeluaran.create') }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm">+ Catat Pengeluaran</a>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3 text-center">Item</th><th class="px-4 py-3 text-right">Total</th><th class="px-4 py-3 text-right">Aksi</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($pengeluaran as $p)
                    <tr>
                        <td class="px-4 py-3">{{ $p->tanggal_pengeluaran->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $p->jenis?->nama ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $p->detail_count }}</td>
                        <td class="px-4 py-3 text-right">Rp{{ number_format($p->total_harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('umkm.pengeluaran.show', $p) }}" class="text-emerald-600 hover:underline">Detail</a>
                            <form action="{{ route('umkm.pengeluaran.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline ml-2">Hapus</button></form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada pengeluaran.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $pengeluaran->links() }}</div>
    </div>
</x-umkm-layout>
