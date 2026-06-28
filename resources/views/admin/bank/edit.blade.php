<x-admin-layout header="Edit Bank">
    <div class="max-w-md bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('admin.bank.update', $item) }}">
            @csrf @method('PUT')
            <label class="block text-sm font-medium mb-1">Nama Bank</label>
            <input name="nama_bank" value="{{ old('nama_bank', $item->nama_bank) }}" required
                   class="w-full rounded-md border-gray-300 text-sm mb-4">
            <div class="flex gap-2">
                <button class="bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors px-4 py-2 text-sm">Perbarui</button>
                <a href="{{ route('admin.bank.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
