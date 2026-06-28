<x-admin-layout header="Dashboard Admin">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php $cards = ['umkm' => ['UMKM','store'], 'produk' => ['Produk','cube'], 'customer' => ['Customer','users'], 'transaksi' => ['Transaksi','inbox']]; @endphp
        @foreach ($cards as $key => [$label, $icon])
            <x-stat-card :label="$label" :value="$stats[$key]" :icon="$icon" tone="indigo" />
        @endforeach
    </div>

    <div class="card mt-6 p-6">
        <h2 class="text-sm font-semibold text-gray-700 mb-3">Manajemen</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            @php $links = [
                ['admin.umkm.index','UMKM','store'],
                ['admin.produk.index','Produk','cube'],
                ['admin.users.index','Users','users'],
                ['admin.bank.index','Bank','bank'],
                ['admin.jenis-usaha.index','Jenis Usaha','briefcase'],
                ['admin.jenis-pengeluaran.index','Jenis Pengeluaran','banknotes'],
                ['admin.kategori-produk.index','Kategori Produk','tag'],
            ]; @endphp
            @foreach ($links as [$r, $lbl, $ic])
                <a href="{{ route($r) }}" class="flex items-center gap-3 px-4 py-3 rounded-lg border border-gray-200 text-gray-700 hover:border-indigo-400 hover:text-indigo-600 hover:bg-indigo-50/50 transition-colors">
                    <x-icon :name="$ic" class="w-5 h-5" /> {{ $lbl }}
                </a>
            @endforeach
        </div>
    </div>
</x-admin-layout>
