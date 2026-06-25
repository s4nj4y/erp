<x-admin-layout header="Produk">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari produk" class="rounded-md border-gray-300 text-sm">
            <select name="umkm" class="rounded-md border-gray-300 text-sm">
                <option value="">Semua UMKM</option>
                @foreach ($umkmList as $u)
                    <option value="{{ $u->id }}" @selected(request('umkm') == $u->id)>{{ $u->nama_umkm }}</option>
                @endforeach
            </select>
            <select name="kategori" class="rounded-md border-gray-300 text-sm">
                <option value="">Semua kategori</option>
                @foreach ($kategoriList as $k)
                    <option value="{{ $k->id }}" @selected(request('kategori') == $k->id)>{{ $k->nama }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-gray-200 rounded-md text-sm">Filter</button>
        </form>
        <a href="{{ route('admin.produk.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm whitespace-nowrap">+ Tambah Produk</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Produk</th>
                    <th class="px-4 py-3">UMKM</th>
                    <th class="px-4 py-3 text-right">Harga</th>
                    <th class="px-4 py-3 text-center">Stok</th>
                    <th class="px-4 py-3">Tampil</th>
                    <th class="px-4 py-3 w-32 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($produk as $p)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="h-10 w-10 bg-gray-100 rounded flex items-center justify-center overflow-hidden text-gray-300 text-xs">
                                    @if ($p->gambar)<img src="{{ asset('storage/'.$p->gambar) }}" class="object-cover h-full w-full">@else -@endif
                                </div>
                                <div>
                                    <div class="font-medium">{{ $p->nama_produk }}</div>
                                    <div class="text-xs text-gray-400">{{ $p->kategori?->nama }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $p->umkm?->nama_umkm }}</td>
                        <td class="px-4 py-3 text-right">Rp{{ number_format($p->harga, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center {{ $p->stok <= 0 ? 'text-red-500 font-semibold' : '' }}">{{ $p->stok }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.produk.toggle', $p) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="{{ $p->show ? 'text-green-600' : 'text-gray-400' }} hover:underline">
                                    {{ $p->show ? 'Ya' : 'Tidak' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.produk.edit', $p) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.produk.destroy', $p) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus produk ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-400">Belum ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $produk->links() }}</div>
    </div>
</x-admin-layout>
