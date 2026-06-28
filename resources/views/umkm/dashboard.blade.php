<x-umkm-layout header="Dashboard">
    @unless ($umkm)
        <div class="flex items-start gap-3 bg-amber-50 border border-amber-200 text-amber-800 p-4 rounded-xl mb-6 text-sm">
            <x-icon name="warning" class="w-5 h-5 mt-0.5 text-amber-600" />
            <p>Profil UMKM Anda belum dibuat.
                <a href="{{ route('umkm.profil.edit') }}" class="font-medium underline">Lengkapi sekarang</a>.</p>
        </div>
    @else
        <div class="card p-5 mb-6 flex items-center gap-4">
            <div class="h-14 w-14 shrink-0 rounded-lg bg-emerald-50 overflow-hidden flex items-center justify-center text-emerald-600">
                @if ($umkm->foto)
                    <img src="{{ asset('storage/'.$umkm->foto) }}" alt="{{ $umkm->nama_umkm }}" class="h-full w-full object-cover">
                @else
                    <x-icon name="store" class="w-7 h-7" />
                @endif
            </div>
            <div class="min-w-0">
                <div class="text-lg font-semibold text-gray-900 truncate">{{ $umkm->nama_umkm }}</div>
                <div class="text-sm text-gray-500 truncate">{{ $umkm->alamat ?: 'Alamat belum diisi' }}</div>
            </div>
        </div>
    @endunless

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-stat-card label="Produk" :value="$stats['produk']" icon="cube" />
        <x-stat-card label="Pesanan" :value="$stats['pesanan']" icon="inbox" />
        <x-stat-card label="Perlu Verifikasi" :value="$stats['perlu_verifikasi']" icon="warning"
                     :tone="$stats['perlu_verifikasi'] ? 'red' : 'emerald'" />
        <x-stat-card label="Pendapatan (selesai)" value="Rp{{ number_format($stats['pendapatan'], 0, ',', '.') }}" icon="wallet" />
    </div>

    <div class="card mt-6 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Aksi Cepat</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
            @php $qa = [['umkm.transaksi.index','Pesanan Masuk','inbox'],['umkm.produk.index','Kelola Produk','cube'],['umkm.profil.edit','Profil & Rekening','user-circle']]; @endphp
            @foreach ($qa as [$r, $lbl, $ic])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-200 text-gray-700 hover:border-emerald-400 hover:text-emerald-600 hover:bg-emerald-50/50 transition-colors">
                    <x-icon :name="$ic" class="w-5 h-5" /> {{ $lbl }}
                </a>
            @endforeach
        </div>
    </div>
</x-umkm-layout>
