<x-public-layout>
    <a href="{{ route('home') }}" class="inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-700">&larr; Kembali ke Daftar UMKM</a>

    {{-- Header toko --}}
    <section class="mt-4 bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="aspect-[3/1] bg-gradient-to-br from-indigo-500 to-violet-600 flex items-center justify-center text-white/70 overflow-hidden">
            @if ($umkm->foto)
                <img src="{{ \Illuminate\Support\Str::startsWith($umkm->foto, ['http://', 'https://']) ? $umkm->foto : asset('storage/'.$umkm->foto) }}"
                     alt="Foto toko {{ $umkm->nama_umkm }}" class="object-cover w-full h-full">
            @else
                <x-icon name="store" class="w-14 h-14" />
            @endif
        </div>

        <div class="p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $umkm->nama_umkm }}</h1>
                    @if ($umkm->jenisUsaha)
                        <span class="mt-1.5 inline-flex rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">
                            {{ $umkm->jenisUsaha->nama_usaha }}
                        </span>
                    @endif
                </div>
                @if ($umkm->no_wa)
                    <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/\D/', '', $umkm->no_wa)) }}"
                       target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-semibold text-white hover:bg-green-700 transition-colors">
                        <x-icon name="external" class="w-4 h-4" /> Hubungi via WhatsApp
                    </a>
                @endif
            </div>

            @if ($umkm->deskripsi)
                <p class="mt-4 text-gray-700 leading-relaxed whitespace-pre-line">{{ $umkm->deskripsi }}</p>
            @endif

            <dl class="mt-5 grid sm:grid-cols-2 gap-x-8 gap-y-3 text-sm border-t border-gray-100 pt-5">
                @if ($umkm->alamat)
                    <div class="flex items-center gap-2">
                        <x-icon name="map-pin" class="w-4 h-4 text-gray-400 shrink-0" />
                        <span class="text-gray-700">{{ $umkm->alamat }}</span>
                    </div>
                @endif
                @if ($umkm->nama_pendiri)
                    <div class="flex items-center gap-2">
                        <x-icon name="user-circle" class="w-4 h-4 text-gray-400 shrink-0" />
                        <span class="text-gray-700">Pendiri: {{ $umkm->nama_pendiri }}</span>
                    </div>
                @endif
                @if ($umkm->tgl_pendirian)
                    <div class="flex items-center gap-2">
                        <x-icon name="briefcase" class="w-4 h-4 text-gray-400 shrink-0" />
                        <span class="text-gray-700">Berdiri {{ $umkm->tgl_pendirian->format('d M Y') }}</span>
                    </div>
                @endif
            </dl>
        </div>
    </section>

    {{-- Produk toko --}}
    <h2 class="text-lg font-semibold text-gray-900 mt-10 mb-4">Produk dari {{ $umkm->nama_umkm }}</h2>

    @if ($produk->isEmpty())
        <div class="rounded-xl border border-dashed border-gray-300 bg-white py-16 text-center">
            <x-icon name="cube" class="w-10 h-10 mx-auto text-gray-300" />
            <p class="mt-3 text-gray-500">Toko ini belum punya produk.</p>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ($produk as $p)
                <x-produk-card :produk="$p" />
            @endforeach
        </div>
        <div class="mt-8">{{ $produk->links() }}</div>
    @endif
</x-public-layout>
