<x-umkm-layout header="Profil & Rekening">
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-200 shadow-sm">
            <h2 class="font-semibold mb-4">Profil UMKM</h2>
            <form method="POST" action="{{ route('umkm.profil.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf @method('PUT')
                @php $m = $umkm; @endphp
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama UMKM</label>
                        <input name="nama_umkm" value="{{ old('nama_umkm', $m?->nama_umkm) }}" required class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Jenis Usaha</label>
                        <select name="jenis_usaha_id" class="w-full rounded-md border-gray-300 text-sm">
                            <option value="">— pilih —</option>
                            @foreach ($jenisUsaha as $j)
                                <option value="{{ $j->id }}" @selected(old('jenis_usaha_id', $m?->jenis_usaha_id) == $j->id)>{{ $j->nama_usaha }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $m?->email) }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">No. WhatsApp</label>
                        <input name="no_wa" value="{{ old('no_wa', $m?->no_wa) }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Tgl Pendirian</label>
                        <input type="date" name="tgl_pendirian" value="{{ old('tgl_pendirian', $m?->tgl_pendirian?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Nama Pendiri</label>
                        <input name="nama_pendiri" value="{{ old('nama_pendiri', $m?->nama_pendiri) }}" class="w-full rounded-md border-gray-300 text-sm">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Alamat</label>
                    <input name="alamat" value="{{ old('alamat', $m?->alamat) }}" class="w-full rounded-md border-gray-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Deskripsi</label>
                    <textarea name="deskripsi" rows="2" class="w-full rounded-md border-gray-300 text-sm">{{ old('deskripsi', $m?->deskripsi) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Foto / Logo</label>
                    @if ($m?->foto)<img src="{{ asset('storage/'.$m->foto) }}" class="h-16 w-16 object-cover rounded mb-2">@endif
                    <input type="file" name="foto" accept="image/*" class="text-sm">
                </div>
                <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm">Simpan Profil</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-xl border border-gray-200 shadow-sm h-fit">
            <h2 class="font-semibold mb-3">Rekening Bank</h2>
            @if (! $umkm)
                <p class="text-sm text-gray-400">Simpan profil dulu untuk menambah rekening.</p>
            @else
                <form method="POST" action="{{ route('umkm.profil.rekening.store') }}" class="space-y-2 mb-4">
                    @csrf
                    <select name="bank_id" required class="w-full rounded-md border-gray-300 text-sm">
                        <option value="">— pilih bank —</option>
                        @foreach ($banks as $b)<option value="{{ $b->id }}">{{ $b->nama_bank }}</option>@endforeach
                    </select>
                    <input name="atas_nama" placeholder="Atas nama" required class="w-full rounded-md border-gray-300 text-sm">
                    <input name="rekening" placeholder="No. rekening" required class="w-full rounded-md border-gray-300 text-sm">
                    <button class="w-full bg-emerald-600 text-white rounded-md py-2 text-sm">Tambah Rekening</button>
                </form>
                <ul class="divide-y divide-gray-100 text-sm">
                    @forelse ($umkm->rekening as $rek)
                        <li class="py-2 flex items-start justify-between">
                            <div>
                                <div class="font-medium">{{ $rek->bank?->nama_bank }}</div>
                                <div class="text-gray-500">{{ $rek->rekening }} · {{ $rek->atas_nama }}</div>
                            </div>
                            <form action="{{ route('umkm.profil.rekening.destroy', $rek) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Hapus</button></form>
                        </li>
                    @empty
                        <li class="py-2 text-gray-400">Belum ada rekening.</li>
                    @endforelse
                </ul>
            @endif
        </div>
    </div>
</x-umkm-layout>
