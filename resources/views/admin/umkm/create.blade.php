<x-admin-layout header="Tambah UMKM">
    <div class="max-w-3xl bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('admin.umkm.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @include('admin.umkm._fields', ['umkm' => null])
            <div class="flex gap-2 pt-2">
                <button class="bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors px-4 py-2 text-sm">Simpan</button>
                <a href="{{ route('admin.umkm.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
