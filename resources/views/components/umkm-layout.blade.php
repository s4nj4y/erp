@props(['header' => 'Toko Saya', 'title' => null])

@php
    $nav = [
        ['umkm.dashboard', 'Dashboard', null, 'dashboard'],
        ['umkm.transaksi.index', 'Pesanan Masuk', 'umkm.transaksi.*', 'inbox'],
        ['umkm.produk.index', 'Produk', 'umkm.produk.*', 'cube'],
        ['umkm.saldo.index', 'Modal / Saldo', 'umkm.saldo.*', 'wallet'],
        ['umkm.pengeluaran.index', 'Pengeluaran', 'umkm.pengeluaran.*', 'banknotes'],
        ['umkm.laporan.laba-rugi', 'Laporan', 'umkm.laporan.*', 'chart'],
        ['umkm.profil.edit', 'Profil & Rekening', 'umkm.profil.*', 'user-circle'],
    ];
@endphp

<x-app-shell
    brand="Toko Saya"
    :title="$title ?? ('UMKM · '.$header)"
    :header="$header"
    :nav="$nav"
    sidebar="bg-emerald-900 text-emerald-100"
    brand-border="border-emerald-800"
    link-hover="hover:bg-emerald-800/60 hover:text-white"
    link-active="bg-emerald-800 text-white"
    active-bar="border-emerald-400"
    accent="hover:text-emerald-600">
    {{ $slot }}
</x-app-shell>
