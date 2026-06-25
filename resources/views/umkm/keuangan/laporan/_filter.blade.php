{{-- $routeName, $title, $periode tersedia dari view pemanggil --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="flex gap-2 text-sm">
        <a href="{{ route('umkm.laporan.laba-rugi') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('umkm.laporan.laba-rugi') ? 'bg-emerald-600 text-white' : 'bg-white border' }}">Laba Rugi</a>
        <a href="{{ route('umkm.laporan.pendapatan') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('umkm.laporan.pendapatan') ? 'bg-emerald-600 text-white' : 'bg-white border' }}">Pendapatan</a>
        <a href="{{ route('umkm.laporan.perubahan-modal') }}" class="px-3 py-1.5 rounded-md {{ request()->routeIs('umkm.laporan.perubahan-modal') ? 'bg-emerald-600 text-white' : 'bg-white border' }}">Perubahan Modal</a>
    </div>
    <div class="flex items-end gap-2">
        <form method="GET" action="{{ route($routeName) }}" class="flex items-end gap-2">
            <div><label class="block text-xs text-gray-500">Dari</label><input type="date" name="from" value="{{ request('from', $from->format('Y-m-d')) }}" class="rounded-md border-gray-300 text-sm"></div>
            <div><label class="block text-xs text-gray-500">Sampai</label><input type="date" name="to" value="{{ request('to', $to->format('Y-m-d')) }}" class="rounded-md border-gray-300 text-sm"></div>
            <button class="px-4 py-2 bg-gray-200 rounded-md text-sm">Terapkan</button>
        </form>
        <a href="{{ route($routeName, array_merge(request()->only('from','to'), ['export'=>'pdf'])) }}" class="px-3 py-2 bg-red-600 text-white rounded-md text-sm">PDF</a>
        <a href="{{ route($routeName, array_merge(request()->only('from','to'), ['export'=>'excel'])) }}" class="px-3 py-2 bg-green-700 text-white rounded-md text-sm">Excel</a>
    </div>
</div>
