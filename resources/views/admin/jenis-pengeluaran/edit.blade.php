<x-admin-layout header="Edit Jenis Pengeluaran">
    <div class="max-w-md bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" action="{{ route('admin.jenis-pengeluaran.update', $item) }}">
            @csrf @method('PUT')
            <label class="block text-sm font-medium mb-1">Nama Jenis Pengeluaran</label>
            <input name="nama" value="{{ old('nama', $item->nama) }}" required
                   class="w-full rounded-md border-gray-300 text-sm mb-4">
            <div class="flex gap-2">
                <button class="bg-indigo-600 text-white rounded-md px-4 py-2 text-sm">Perbarui</button>
                <a href="{{ route('admin.jenis-pengeluaran.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
