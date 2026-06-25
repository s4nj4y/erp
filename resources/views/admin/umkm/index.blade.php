<x-admin-layout header="UMKM">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama UMKM" class="rounded-md border-gray-300 text-sm">
            <button class="px-4 py-2 bg-gray-200 rounded-md text-sm">Cari</button>
        </form>
        <a href="{{ route('admin.umkm.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Tambah UMKM</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama UMKM</th>
                    <th class="px-4 py-3">Jenis Usaha</th>
                    <th class="px-4 py-3 text-center">Produk</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 w-40 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($umkm as $u)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $u->nama_umkm }}</div>
                            <div class="text-xs text-gray-400">{{ $u->alamat }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $u->jenisUsaha?->nama_usaha ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">{{ $u->produk_count }}</td>
                        <td class="px-4 py-3">
                            <form action="{{ route('admin.umkm.toggle', $u) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="{{ $u->status ? 'text-green-600' : 'text-red-500' }} hover:underline">
                                    {{ $u->status ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.umkm.edit', $u) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.umkm.destroy', $u) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus UMKM ini? Semua produk terkait ikut terhapus.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Belum ada UMKM.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $umkm->links() }}</div>
    </div>
</x-admin-layout>
