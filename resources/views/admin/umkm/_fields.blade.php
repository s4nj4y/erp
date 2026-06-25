@php $m = $umkm ?? null; @endphp
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Nama UMKM</label>
        <input name="nama_umkm" value="{{ old('nama_umkm', $m?->nama_umkm) }}" required class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Pemilik (akun role UMKM)</label>
        <select name="user_id" class="w-full rounded-md border-gray-300 text-sm">
            <option value="">— belum ditautkan —</option>
            @foreach ($users as $usr)
                <option value="{{ $usr->id }}" @selected(old('user_id', $m?->user_id) == $usr->id)>{{ $usr->name }} ({{ $usr->email }})</option>
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
        <label class="block text-sm font-medium mb-1">Jenis Usaha</label>
        <select name="jenis_usaha_id" class="w-full rounded-md border-gray-300 text-sm">
            <option value="">— pilih —</option>
            @foreach ($jenisUsaha as $j)
                <option value="{{ $j->id }}" @selected(old('jenis_usaha_id', $m?->jenis_usaha_id) == $j->id)>{{ $j->nama_usaha }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tgl Pendirian</label>
        <input type="date" name="tgl_pendirian" value="{{ old('tgl_pendirian', $m?->tgl_pendirian?->format('Y-m-d')) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Nama Pendiri</label>
        <input name="nama_pendiri" value="{{ old('nama_pendiri', $m?->nama_pendiri) }}" class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="w-full rounded-md border-gray-300 text-sm">
            <option value="1" @selected(old('status', $m?->status ?? 1) == 1)>Aktif</option>
            <option value="0" @selected(old('status', $m?->status ?? 1) == 0)>Nonaktif</option>
        </select>
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
    @if ($m?->foto)
        <img src="{{ asset('storage/'.$m->foto) }}" class="h-16 w-16 object-cover rounded mb-2">
    @endif
    <input type="file" name="foto" accept="image/*" class="text-sm">
</div>
