<x-umkm-layout header="Edit Produk">
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <form method="POST" action="{{ route('umkm.produk.update', $produk) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                @include('umkm.produk._fields', ['produk' => $produk])
                <div class="flex gap-2 pt-2">
                    <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm">Perbarui</button>
                    <a href="{{ route('umkm.produk.index') }}" class="px-4 py-2 text-sm text-gray-600">Kembali</a>
                </div>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm h-fit">
            <h2 class="font-semibold mb-1">Stok</h2>
            <p class="text-2xl font-bold text-emerald-600 mb-3">{{ $produk->stok }}</p>
            <form method="POST" action="{{ route('umkm.produk.stok.store', $produk) }}" class="space-y-2 mb-4">
                @csrf
                <select name="status" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="masuk">Stok Masuk</option>
                    <option value="keluar">Stok Keluar</option>
                </select>
                <input type="number" name="jumlah" min="1" placeholder="Jumlah" required class="w-full rounded-md border-gray-300 text-sm">
                <input type="date" name="tanggal" value="{{ date('Y-m-d') }}" required class="w-full rounded-md border-gray-300 text-sm">
                <input name="keterangan" placeholder="Keterangan (opsional)" class="w-full rounded-md border-gray-300 text-sm">
                <button class="w-full bg-emerald-600 text-white rounded-md py-2 text-sm">Catat</button>
            </form>
            <h3 class="text-sm font-medium mb-2">Riwayat</h3>
            <ul class="divide-y divide-gray-100 text-sm max-h-64 overflow-auto">
                @forelse ($produk->stoks->sortByDesc('tanggal') as $s)
                    <li class="py-2 flex items-center justify-between">
                        <div>
                            <span class="{{ $s->status === 'masuk' ? 'text-green-600' : 'text-red-500' }}">{{ $s->status === 'masuk' ? '+'.$s->jumlah_masuk : '-'.$s->jumlah_keluar }}</span>
                            <span class="text-xs text-gray-400 ml-1">{{ $s->tanggal->format('d/m/Y') }}</span>
                        </div>
                        <form action="{{ route('umkm.produk.stok.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline text-xs">Hapus</button></form>
                    </li>
                @empty
                    <li class="py-2 text-gray-400">Belum ada pergerakan.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-umkm-layout>
