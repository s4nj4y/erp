<x-admin-layout header="Edit UMKM">
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <form method="POST" action="{{ route('admin.umkm.update', $umkm) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                @include('admin.umkm._fields', ['umkm' => $umkm])
                <div class="flex gap-2 pt-2">
                    <button class="bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors px-4 py-2 text-sm">Perbarui</button>
                    <a href="{{ route('admin.umkm.index') }}" class="px-4 py-2 text-sm text-gray-600">Kembali</a>
                </div>
            </form>
        </div>

        {{-- Rekening bank --}}
        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm h-fit">
            <h2 class="font-semibold mb-3">Rekening Bank</h2>
            <form method="POST" action="{{ route('admin.umkm.rekening.store', $umkm) }}" class="space-y-2 mb-4">
                @csrf
                <select name="bank_id" required class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">— pilih bank —</option>
                    @foreach ($banks as $b)
                        <option value="{{ $b->id }}">{{ $b->nama_bank }}</option>
                    @endforeach
                </select>
                <input name="atas_nama" placeholder="Atas nama" required class="w-full rounded-md border-gray-300 text-sm">
                <input name="rekening" placeholder="No. rekening" required class="w-full rounded-md border-gray-300 text-sm">
                <button class="w-full bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition-colors py-2 text-sm">Tambah Rekening</button>
            </form>

            <ul class="divide-y divide-gray-100 text-sm">
                @forelse ($umkm->rekening as $rek)
                    <li class="py-2 flex items-start justify-between">
                        <div>
                            <div class="font-medium">{{ $rek->bank?->nama_bank }}</div>
                            <div class="text-gray-500">{{ $rek->rekening }} · {{ $rek->atas_nama }}</div>
                        </div>
                        <form action="{{ route('admin.umkm.rekening.destroy', $rek) }}" method="POST"
                              onsubmit="return confirm('Hapus rekening ini?')">
                            @csrf @method('DELETE')
                            <button class="text-red-600 hover:underline">Hapus</button>
                        </form>
                    </li>
                @empty
                    <li class="py-2 text-gray-400">Belum ada rekening.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-admin-layout>
