<x-admin-layout header="Kelola Kategori Produk">
    <div class="grid md:grid-cols-2 gap-6">
        {{-- Edit kategori --}}
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="font-semibold mb-3">Detail Kategori</h2>
            <form method="POST" action="{{ route('admin.kategori-produk.update', $item) }}">
                @csrf @method('PUT')
                <label class="block text-sm font-medium mb-1">Nama Kategori</label>
                <input name="nama" value="{{ old('nama', $item->nama) }}" required
                       class="w-full rounded-md border-gray-300 text-sm mb-4">
                <div class="flex gap-2">
                    <button class="bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors px-4 py-2 text-sm">Perbarui</button>
                    <a href="{{ route('admin.kategori-produk.index') }}" class="px-4 py-2 text-sm text-gray-600">Kembali</a>
                </div>
            </form>
        </div>

        {{-- Atribut kategori --}}
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="font-semibold mb-3">Atribut Produk</h2>
            <form method="POST" action="{{ route('admin.kategori-produk.atribut.store', $item) }}" class="flex gap-2 mb-4">
                @csrf
                <input name="atribut_produk" placeholder="Nama atribut (mis. Warna)" required
                       class="flex-1 rounded-md border-gray-300 text-sm">
                <button class="bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors px-3 text-sm">Tambah</button>
            </form>

            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($item->atribut as $atr)
                    <li class="flex items-center justify-between py-2">
                        <span>{{ $atr->atribut_produk }}</span>
                        <form action="{{ route('admin.kategori-produk.atribut.destroy', $atr) }}" method="POST"
                              onsubmit="return confirm('Hapus atribut ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </li>
                @empty
                    <li class="py-2 text-gray-400">Belum ada atribut.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
