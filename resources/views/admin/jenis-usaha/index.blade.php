<x-admin-layout header="Master · Jenis Usaha">
    <div class="grid md:grid-cols-3 gap-6">
        <div class="md:col-span-1">
            <form method="POST" action="{{ route('admin.jenis-usaha.store') }}" class="bg-white p-4 rounded-lg shadow-sm">
                @csrf
                <h2 class="font-semibold mb-3">Tambah Jenis Usaha</h2>
                <input name="nama_usaha" value="{{ old('nama_usaha') }}" placeholder="Nama jenis usaha" required
                       class="w-full rounded-md border-gray-300 text-sm mb-3">
                <button class="w-full bg-indigo-600 text-white rounded-md py-2 text-sm">Simpan</button>
            </form>
        </div>

        <div class="md:col-span-2 bg-white rounded-lg shadow-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr><th class="px-4 py-3">Nama</th><th class="px-4 py-3 w-32 text-right">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $item)
                        <tr>
                            <td class="px-4 py-3">{{ $item->nama_usaha }}</td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.jenis-usaha.edit', $item) }}" class="text-indigo-600 hover:underline">Edit</a>
                                <form action="{{ route('admin.jenis-usaha.destroy', $item) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus data ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 hover:underline ml-2">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="px-4 py-6 text-center text-gray-400">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-4">{{ $items->links() }}</div>
        </div>
    </div>
</x-admin-layout>
