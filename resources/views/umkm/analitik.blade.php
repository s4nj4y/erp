<x-umkm-layout header="Analitik">
    @php
        $rp = fn ($n) => 'Rp'.number_format($n, 0, ',', '.');
        // Label pendek: '05/07' untuk harian, 'Jul 25' untuk bulanan
        $labels = array_map(fn ($l) => strlen($l) === 7
            ? \Illuminate\Support\Carbon::parse($l.'-01')->translatedFormat('M y')
            : \Illuminate\Support\Carbon::parse($l)->format('d/m'), $tren['labels']);
        $grid = ['color' => 'rgba(0,0,0,.05)'];
        $opsiChart = ['plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'grid' => $grid, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false]]]];
        $chartOmzet = ['type' => 'line',
            'data' => ['labels' => $labels, 'datasets' => [[
                'label' => 'Omzet (Rp)', 'data' => $tren['omzet'],
                'borderColor' => '#059669', 'backgroundColor' => 'rgba(5,150,105,.08)',
                'borderWidth' => 2, 'fill' => true, 'tension' => 0.3, 'pointRadius' => 0, 'pointHitRadius' => 12,
            ]]],
            'options' => $opsiChart];
        $chartTransaksi = ['type' => 'bar',
            'data' => ['labels' => $labels, 'datasets' => [[
                'label' => 'Transaksi', 'data' => $tren['transaksi'],
                'backgroundColor' => '#6366f1', 'borderRadius' => 4,
            ]]],
            'options' => $opsiChart];
    @endphp

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @foreach (['7d' => '7 Hari Terakhir', '30d' => '30 Hari Terakhir', '12m' => '12 Bulan Terakhir'] as $key => $label)
            <a href="{{ route('umkm.analitik', ['periode' => $key]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $periode === $key
                    ? 'bg-emerald-600 text-white'
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Omzet" :value="$rp($tren['total_omzet'])" icon="banknotes" />
        <x-stat-card label="Transaksi" :value="$tren['total_transaksi']" icon="inbox" tone="indigo" />
        <x-stat-card label="Rata-rata / Transaksi" :value="$rp($aov)" icon="chart" tone="amber" />
        <x-stat-card label="Pelanggan Baru" :value="$pelanggan['pelanggan_baru']" icon="users" tone="emerald" />
    </div>

    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        <div class="card p-6">
            <h2 class="font-bold mb-4">Tren Omzet</h2>
            <canvas height="220" data-chart='@json($chartOmzet)'></canvas>
        </div>
        <div class="card p-6">
            <h2 class="font-bold mb-4">Jumlah Transaksi</h2>
            <canvas height="220" data-chart='@json($chartTransaksi)'></canvas>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="card p-6">
            <h2 class="font-bold mb-4">Produk Terlaris</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr><th class="py-2">Produk</th><th class="py-2 text-right">Terjual</th><th class="py-2 text-right">Nilai</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($produk as $p)
                        <tr>
                            <td class="py-2">{{ $p->nama }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $p->terjual }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $rp($p->nilai) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada penjualan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-bold">Pelanggan Teratas</h2>
                <span class="text-xs text-gray-500">{{ $pelanggan['pelanggan_baru'] }} baru · {{ $pelanggan['pelanggan_lama'] }} lama</span>
            </div>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr><th class="py-2">Pelanggan</th><th class="py-2 text-right">Transaksi</th><th class="py-2 text-right">Total Belanja</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($pelanggan['top'] as $c)
                        <tr>
                            <td class="py-2">{{ $c->nama }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $c->transaksi }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $rp($c->belanja) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada pelanggan pada periode ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-umkm-layout>
