@props(['umkm'])

<a href="{{ route('shop', ['umkm' => $umkm->id]) }}"
   class="group flex flex-col bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all">
    {{-- Banner toko --}}
    <div class="aspect-[16/9] overflow-hidden flex items-center justify-center
                bg-gradient-to-br from-indigo-500 to-violet-600 text-white/70">
        @if ($umkm->foto)
            <img src="{{ \Illuminate\Support\Str::startsWith($umkm->foto, ['http://', 'https://']) ? $umkm->foto : asset('storage/'.$umkm->foto) }}"
                 alt="Foto toko {{ $umkm->nama_umkm }}" loading="lazy"
                 class="object-cover w-full h-full motion-safe:group-hover:scale-105 transition-transform duration-300">
        @else
            <x-icon name="store" class="w-12 h-12" />
        @endif
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-semibold text-gray-900 truncate">{{ $umkm->nama_umkm }}</h3>
            @if ($umkm->jenisUsaha)
                <span class="shrink-0 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                    {{ $umkm->jenisUsaha->nama_usaha }}
                </span>
            @endif
        </div>

        {{-- Deskripsi toko --}}
        <p class="mt-1.5 text-sm text-gray-500 line-clamp-2 {{ $umkm->deskripsi ? '' : 'italic text-gray-400' }}">
            {{ $umkm->deskripsi ?: 'Belum ada deskripsi toko.' }}
        </p>

        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-500">
            <span class="inline-flex items-center gap-1 min-w-0">
                <x-icon name="map-pin" class="w-4 h-4 text-gray-400 shrink-0" />
                <span class="truncate">{{ $umkm->alamat ?: 'Lokasi belum diisi' }}</span>
            </span>
            <span class="inline-flex items-center gap-1 shrink-0 text-gray-600">
                <x-icon name="cube" class="w-4 h-4 text-gray-400" />
                <span class="tabular-nums">{{ $umkm->produk_count }}</span> produk
            </span>
        </div>
    </div>
</a>
