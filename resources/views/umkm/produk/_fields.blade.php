@php $p = $produk ?? null; @endphp
<div class="grid md:grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Nama Produk</label>
        <input name="nama_produk" value="{{ old('nama_produk', $p?->nama_produk) }}" required class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Kategori</label>
        <select name="kategori_produk_id" class="w-full rounded-md border-gray-300 text-sm">
            <option value="">— pilih —</option>
            @foreach ($kategoriList as $k)
                <option value="{{ $k->id }}" @selected(old('kategori_produk_id', $p?->kategori_produk_id) == $k->id)>{{ $k->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Stok Awal</label>
        <input type="number" name="stok" value="{{ old('stok', $p?->stok ?? 0) }}" min="0" required class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Tampilkan di katalog?</label>
        <select name="show" class="w-full rounded-md border-gray-300 text-sm">
            <option value="1" @selected(old('show', $p?->show ?? 1) == 1)>Ya</option>
            <option value="0" @selected(old('show', $p?->show ?? 1) == 0)>Tidak</option>
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Harga Modal</label>
        <input type="number" name="harga_modal" value="{{ old('harga_modal', $p?->harga_modal ?? 0) }}" min="0" required class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Harga Jual</label>
        <input type="number" name="harga" value="{{ old('harga', $p?->harga ?? 0) }}" min="0" required class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<div>
    <label class="block text-sm font-medium mb-1">Deskripsi</label>
    <textarea name="deskripsi" rows="3" class="w-full rounded-md border-gray-300 text-sm">{{ old('deskripsi', $p?->deskripsi) }}</textarea>
</div>
<div class="grid md:grid-cols-5 gap-3">
    @foreach (['berat' => 'Berat', 'kandungan' => 'Kandungan', 'warna' => 'Warna', 'bahan' => 'Bahan', 'ukuran' => 'Ukuran'] as $f => $lbl)
        <div>
            <label class="block text-sm font-medium mb-1">{{ $lbl }}</label>
            <input name="{{ $f }}" value="{{ old($f, $p?->$f) }}" class="w-full rounded-md border-gray-300 text-sm">
        </div>
    @endforeach
</div>
<div>
    <label class="block text-sm font-medium mb-1">Gambar</label>
    @if ($p?->gambar)<img src="{{ asset('storage/'.$p->gambar) }}" class="h-16 w-16 object-cover rounded mb-2">@endif
    <input type="file" name="gambar" accept="image/*" class="text-sm">
</div>
