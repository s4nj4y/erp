<x-umkm-layout header="Analitik">
    @php
        $rp = fn ($n) => 'Rp'.number_format($n, 0, ',', '.');
        // Label pendek: '05/07' untuk harian, 'Jul 25' untuk bulanan
        $fmtLabel = fn ($l) => strlen($l) === 7
            ? \Illuminate\Support\Carbon::parse($l.'-01')->translatedFormat('M y')
            : \Illuminate\Support\Carbon::parse($l)->format('d/m');
        $labels = array_map($fmtLabel, $tren['labels']);

        // Sambung garis proyeksi (dashed) ke ujung deret historis
        $forecast = $prediksi['omzet'];
        $labelsOmzet = $forecast ? array_merge($labels, array_map($fmtLabel, $forecast['labels'])) : $labels;
        $omzetHistori = $forecast
            ? array_merge($tren['omzet'], array_fill(0, count($forecast['nilai']), null))
            : $tren['omzet'];
        $omzetProyeksi = $forecast
            ? array_merge(array_fill(0, count($labels) - 1, null), [end($tren['omzet'])], $forecast['nilai'])
            : null;
        $grid = ['color' => 'rgba(0,0,0,.05)'];
        $opsiChart = ['plugins' => ['legend' => ['display' => false]],
            'scales' => ['y' => ['beginAtZero' => true, 'grid' => $grid, 'ticks' => ['precision' => 0]], 'x' => ['grid' => ['display' => false]]]];
        $datasetOmzet = [[
            'label' => 'Omzet (Rp)', 'data' => $omzetHistori,
            'borderColor' => '#059669', 'backgroundColor' => 'rgba(5,150,105,.08)',
            'borderWidth' => 2, 'fill' => true, 'tension' => 0.3, 'pointRadius' => 0, 'pointHitRadius' => 12,
        ]];
        if ($omzetProyeksi) {
            $datasetOmzet[] = [
                'label' => 'Proyeksi', 'data' => $omzetProyeksi,
                'borderColor' => '#059669', 'borderDash' => [6, 4],
                'borderWidth' => 2, 'fill' => false, 'tension' => 0.3, 'pointRadius' => 0, 'pointHitRadius' => 12,
            ];
        }
        $chartOmzet = ['type' => 'line',
            'data' => ['labels' => $labelsOmzet, 'datasets' => $datasetOmzet],
            'options' => $omzetProyeksi
                ? array_replace_recursive($opsiChart, ['plugins' => ['legend' => ['display' => true]]])
                : $opsiChart];
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

    <div class="grid lg:grid-cols-3 gap-4 mb-6">
        <div class="card p-6">
            <h2 class="font-bold mb-1">Prediksi Omzet</h2>
            @if ($forecast)
                <p class="text-xs text-gray-500 mb-3">Proyeksi {{ $forecast['horizon'] }} ke depan (regresi linear)</p>
                <div class="text-2xl font-bold text-emerald-600 tabular-nums">{{ $rp($forecast['total']) }}</div>
                <p class="text-sm text-gray-500 mt-2">Garis putus-putus pada grafik Tren Omzet menunjukkan proyeksi harian/bulanannya.</p>
            @else
                <p class="text-sm text-gray-400 mt-3">Data penjualan belum cukup untuk prediksi (butuh riwayat pada periode ini).</p>
            @endif
        </div>
        <div class="card p-6">
            <h2 class="font-bold mb-4">Stok Segera Habis</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr><th class="py-2">Produk</th><th class="py-2 text-right">Stok</th><th class="py-2 text-right">± Habis Dalam</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($prediksi['stok'] as $s)
                        <tr>
                            <td class="py-2">{{ $s->nama }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $s->stok }}</td>
                            <td class="py-2 text-right tabular-nums {{ $s->hari_tersisa <= 7 ? 'text-red-500 font-medium' : '' }}">{{ $s->hari_tersisa }} hari</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada penjualan 30 hari terakhir.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card p-6">
            <h2 class="font-bold mb-4">Produk Trending</h2>
            <table class="w-full text-sm">
                <thead class="text-left text-gray-500">
                    <tr><th class="py-2">Produk</th><th class="py-2 text-right">Terjual 30 Hari</th><th class="py-2 text-right">Momentum</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($prediksi['trending'] as $t)
                        <tr>
                            <td class="py-2">{{ $t->nama }}</td>
                            <td class="py-2 text-right tabular-nums">{{ $t->terjual }}</td>
                            <td class="py-2 text-right tabular-nums text-emerald-600">+{{ $t->slope }}/hari</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-6 text-center text-gray-400">Belum ada produk dengan tren naik.</td></tr>
                    @endforelse
                </tbody>
            </table>
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
