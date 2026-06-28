<x-umkm-layout header="Tambah Produk">
    <div class="max-w-3xl bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
        <form method="POST" action="{{ route('umkm.produk.store') }}" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @include('umkm.produk._fields', ['produk' => null])
            <div class="flex gap-2 pt-2">
                <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm">Simpan</button>
                <a href="{{ route('umkm.produk.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-umkm-layout>
