<x-umkm-layout header="Dashboard">
    @unless ($umkm)
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg mb-6 text-sm">
            Profil UMKM Anda belum dibuat.
            <a href="{{ route('umkm.profil.edit') }}" class="font-medium underline">Lengkapi sekarang</a>.
        </div>
    @else
        <div class="bg-white p-6 rounded-lg shadow-sm mb-6 flex items-center gap-4">
            @if ($umkm->foto)
                <img src="{{ asset('storage/'.$umkm->foto) }}" class="h-14 w-14 rounded object-cover">
            @endif
            <div>
                <div class="text-lg font-semibold">{{ $umkm->nama_umkm }}</div>
                <div class="text-sm text-gray-500">{{ $umkm->alamat }}</div>
            </div>
        </div>
    @endunless

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="text-3xl font-bold text-emerald-600">{{ $stats['produk'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Produk</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="text-3xl font-bold text-emerald-600">{{ $stats['pesanan'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Pesanan</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="text-3xl font-bold {{ $stats['perlu_verifikasi'] ? 'text-red-500' : 'text-emerald-600' }}">{{ $stats['perlu_verifikasi'] }}</div>
            <div class="text-sm text-gray-500 mt-1">Perlu Verifikasi</div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm">
            <div class="text-2xl font-bold text-emerald-600">Rp{{ number_format($stats['pendapatan'], 0, ',', '.') }}</div>
            <div class="text-sm text-gray-500 mt-1">Pendapatan (selesai)</div>
        </div>
    </div>

    <div class="bg-white mt-6 p-6 rounded-lg shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <a href="{{ route('umkm.transaksi.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-emerald-400 hover:text-emerald-600">Pesanan Masuk</a>
            <a href="{{ route('umkm.produk.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-emerald-400 hover:text-emerald-600">Kelola Produk</a>
            <a href="{{ route('umkm.profil.edit') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-emerald-400 hover:text-emerald-600">Profil & Rekening</a>
        </div>
    </div>
</x-umkm-layout>
