<x-umkm-layout header="Modal / Saldo">
    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-1 space-y-4">
            <div class="bg-emerald-600 text-white p-6 rounded-lg shadow-sm">
                <div class="text-sm opacity-80">Modal Saat Ini</div>
                <div class="text-2xl font-bold">Rp{{ number_format($modalSaatIni, 0, ',', '.') }}</div>
            </div>
            <form method="POST" action="{{ route('umkm.saldo.store') }}" class="bg-white p-4 rounded-lg shadow-sm space-y-2">
                @csrf
                <h2 class="font-semibold">Catat Modal</h2>
                <select name="jenis_transaksi" required class="w-full rounded-md border-gray-300 text-sm">
                    <option value="investasi_awal">Investasi Awal</option>
                    <option value="penambahan_modal">Penambahan Modal</option>
                    <option value="pengambilan_modal">Pengambilan Modal</option>
                </select>
                <input type="number" name="jumlah" min="1" placeholder="Jumlah (Rp)" required class="w-full rounded-md border-gray-300 text-sm">
                <input type="date" name="tanggal_transaksi" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 text-sm">
                <input name="keterangan" placeholder="Keterangan (opsional)" class="w-full rounded-md border-gray-300 text-sm">
                <button class="w-full bg-emerald-600 text-white rounded-md py-2 text-sm">Simpan</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr><th class="px-4 py-3">Tanggal</th><th class="px-4 py-3">Jenis</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Saldo</th><th class="px-4 py-3"></th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($saldo as $s)
                        <tr>
                            <td class="px-4 py-3">{{ $s->tanggal_transaksi->format('d/m/Y') }}</td>
                            <td class="px-4 py-3">
                                <div>{{ ucwords(str_replace('_', ' ', $s->jenis_transaksi)) }}</div>
                                @if ($s->keterangan)<div class="text-xs text-gray-400">{{ $s->keterangan }}</div>@endif
                            </td>
                            <td class="px-4 py-3 text-right {{ $s->jenis_transaksi === 'pengambilan_modal' ? 'text-red-500' : 'text-green-600' }}">
                                {{ $s->jenis_transaksi === 'pengambilan_modal' ? '-' : '+' }}Rp{{ number_format($s->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right">Rp{{ number_format($s->saldo, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('umkm.saldo.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs">Hapus</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada catatan modal.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $saldo->links() }}</div>
        </div>
    </div>
</x-umkm-layout>
