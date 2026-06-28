<x-admin-layout header="Produk">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <label for="q" class="sr-only">Cari produk</label>
            <input id="q" name="q" value="{{ request('q') }}" placeholder="Cari produk" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select name="umkm" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua UMKM</option>
                @foreach ($umkmList as $u)
                    <option value="{{ $u->id }}" @selected(request('umkm') == $u->id)>{{ $u->nama_umkm }}</option>
                @endforeach
            </select>
            <select name="kategori" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua kategori</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
        </form>
        <a href="{{ route('admin.produk.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors whitespace-nowrap">
            <x-icon name="cube" class="w-4 h-4" /> Tambah Produk
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Produk</th><th class="px-4 py-3 font-medium">UMKM</th>
                        <th class="px-4 py-3 font-medium text-right">Harga</th><th class="px-4 py-3 font-medium text-center">Stok</th>
                        <th class="px-4 py-3 font-medium">Tampil</th><th class="px-4 py-3 font-medium w-32 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($produk as $p)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 shrink-0 bg-gray-100 rounded-lg flex items-center justify-center overflow-hidden text-gray-300">
                                        @if ($p->gambar)<img src="{{ asset('storage/'.$p->gambar) }}" alt="" class="object-cover h-full w-full">@else<x-icon name="cube" class="w-5 h-5" />@endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-medium text-gray-900 truncate">{{ $p->nama_produk }}</div>
                                        <div class="text-xs text-gray-400">{{ $p->kategori?->nama ?: '—' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ $p->umkm?->nama_umkm }}</td>
                            <td class="px-4 py-3 text-right tabular-nums whitespace-nowrap">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center tabular-nums {{ $p->stok <= 0 ? 'text-red-500 font-semibold' : 'text-gray-700' }}">{{ $p->stok }}</td>
                            <td class="px-4 py-3">
                                <form action="{{ route('admin.produk.toggle', $p) }}" method="POST">@csrf @method('PATCH')
                                    <button class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $p->show ? 'bg-green-50 text-green-700 ring-green-200' : 'bg-gray-100 text-gray-500 ring-gray-200' }}">
                                        {{ $p->show ? 'Tampil' : 'Disembunyikan' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.produk.edit', $p) }}" class="font-medium text-indigo-600 hover:text-indigo-700">Edit</a>
                                <form action="{{ route('admin.produk.destroy', $p) }}" method="POST" class="inline" onsubmit="return confirm('Hapus produk ini?')">@csrf @method('DELETE')<button class="ml-3 font-medium text-red-600 hover:text-red-700">Hapus</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">
                            <x-icon name="cube" class="w-9 h-9 mx-auto text-gray-300" />
                            <p class="mt-2">Belum ada produk.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $produk->links() }}</div>
    </div>
</x-admin-layout>
