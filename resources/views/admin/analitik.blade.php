<x-admin-layout header="Analitik Platform">
    @php
        $rp = fn ($n) => 'Rp'.number_format($n, 0, ',', '.');
        $fmtLabel = fn ($l) => strlen($l) === 7
            ? \Illuminate\Support\Carbon::parse($l.'-01')->translatedFormat('M y')
            : \Illuminate\Support\Carbon::parse($l)->format('d/m');
        $labels = array_map($fmtLabel, $pertumbuhan['labels']);

        // Sambung garis proyeksi GMV (dashed) ke deret historis
        $forecast = $prediksi['gmv'];
        $labelsGmv = $forecast ? array_merge($labels, array_map($fmtLabel, $forecast['labels'])) : $labels;
        $gmvHistori = $forecast
            ? array_merge($pertumbuhan['gmv'], array_fill(0, count($forecast['nilai']), null))
            : $pertumbuhan['gmv'];
        $gmvProyeksi = $forecast
            ? array_merge(array_fill(0, count($labels) - 1, null), [end($pertumbuhan['gmv'])], $forecast['nilai'])
            : null;
        $grid = ['color' => 'rgba(0,0,0,.05)'];
        $sumbu = ['y' => ['beginAtZero' => true, 'grid' => $grid, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false]]];
        $datasetGmv = [[
            'label' => 'GMV (Rp)', 'data' => $gmvHistori,
            'borderColor' => '#059669', 'backgroundColor' => 'rgba(5,150,105,.08)',
            'borderWidth' => 2, 'fill' => true, 'tension' => 0.3, 'pointRadius' => 0, 'pointHitRadius' => 12,
        ]];
        if ($gmvProyeksi) {
            $datasetGmv[] = [
                'label' => 'Proyeksi', 'data' => $gmvProyeksi,
                'borderColor' => '#059669', 'borderDash' => [6, 4],
                'borderWidth' => 2, 'fill' => false, 'tension' => 0.3, 'pointRadius' => 0, 'pointHitRadius' => 12,
            ];
        }
        $chartGmv = ['type' => 'line',
            'data' => ['labels' => $labelsGmv, 'datasets' => $datasetGmv],
            'options' => ['plugins' => ['legend' => ['display' => (bool) $gmvProyeksi]], 'scales' => $sumbu]];
        $chartDaftar = ['type' => 'bar',
            'data' => ['labels' => $labels, 'datasets' => [
                ['label' => 'UMKM', 'data' => $pertumbuhan['umkm_baru'], 'backgroundColor' => '#059669', 'borderRadius' => 4],
                ['label' => 'Customer', 'data' => $pertumbuhan['customer_baru'], 'backgroundColor' => '#6366f1', 'borderRadius' => 4],
            ]],
            'options' => ['scales' => $sumbu]];
    @endphp

    <div class="flex flex-wrap items-center gap-2 mb-6">
        @foreach (['7d' => '7 Hari Terakhir', '30d' => '30 Hari Terakhir', '12m' => '12 Bulan Terakhir'] as $key => $label)
            <a href="{{ route('admin.analitik', ['periode' => $key]) }}"
               class="px-3 py-1.5 rounded-lg text-sm font-medium {{ $periode === $key
                    ? 'bg-indigo-600 text-white'
                    : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50' }}">{{ $label }}</a>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="GMV (Nilai Penjualan)" :value="$rp($pertumbuhan['total_gmv'])" icon="banknotes" />
        <x-stat-card label="Transaksi" :value="$pertumbuhan['total_transaksi']" icon="inbox" tone="indigo" />
        <x-stat-card label="UMKM Baru" :value="$pertumbuhan['total_umkm_baru']" icon="store" tone="amber" />
        <x-stat-card label="Customer Baru" :value="$pertumbuhan['total_customer_baru']" icon="users" tone="indigo" />
    </div>

    <div class="grid lg:grid-cols-2 gap-4 mb-6">
        <div class="card p-6">
            <h2 class="font-bold mb-4">Tren GMV</h2>
            <canvas height="220" data-chart='@json($chartGmv)'></canvas>
        </div>
        <div class="card p-6">
            <h2 class="font-bold mb-4">Pendaftaran Baru</h2>
            <canvas height="220" data-chart='@json($chartDaftar)'></canvas>
        </div>
    </div>

    <div class="card p-6 mb-6 max-w-3xl">
        <h2 class="font-bold mb-1">Prediksi Platform</h2>
        @if ($forecast || $prediksi['umkm_baru'] || $prediksi['customer_baru'])
            <p class="text-xs text-gray-500 mb-3">Proyeksi {{ ($forecast ?? $prediksi['umkm_baru'] ?? $prediksi['customer_baru'])['horizon'] }} ke depan (regresi linear)</p>
            <div class="grid grid-cols-3 gap-4 text-center">
                <div>
                    <div class="text-xl font-bold text-emerald-600 tabular-nums">{{ $forecast ? $rp($forecast['total']) : '—' }}</div>
                    <div class="text-sm text-gray-500 mt-1">GMV</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ $prediksi['umkm_baru']['total'] ?? '—' }}</div>
                    <div class="text-sm text-gray-500 mt-1">UMKM Baru</div>
                </div>
                <div>
                    <div class="text-xl font-bold text-gray-900 tabular-nums">{{ $prediksi['customer_baru']['total'] ?? '—' }}</div>
                    <div class="text-sm text-gray-500 mt-1">Customer Baru</div>
                </div>
            </div>
        @else
            <p class="text-sm text-gray-400 mt-3">Data belum cukup untuk prediksi (butuh riwayat pada periode ini).</p>
        @endif
    </div>

    <div class="card p-6 max-w-3xl">
        <h2 class="font-bold mb-4">UMKM Teratas</h2>
        <table class="w-full text-sm">
            <thead class="text-left text-gray-500">
                <tr><th class="py-2">UMKM</th><th class="py-2 text-right">Transaksi</th><th class="py-2 text-right">GMV</th></tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($umkmTeratas as $u)
                    <tr>
                        <td class="py-2">{{ $u->nama }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $u->transaksi }}</td>
                        <td class="py-2 text-right tabular-nums">{{ $rp($u->gmv) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada penjualan pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-admin-layout>
