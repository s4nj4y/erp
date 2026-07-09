@props(['header' => 'Master Data', 'title' => null])

@php
    $nav = [
        ['admin.dashboard', 'Dashboard', null, 'dashboard'],
        ['admin.analitik', 'Analitik', 'admin.analitik', 'trending'],
        ['admin.umkm.index', 'UMKM', 'admin.umkm.*', 'store'],
        ['admin.produk.index', 'Produk', 'admin.produk.*', 'cube'],
        ['admin.users.index', 'Users', 'admin.users.*', 'users'],
        ['admin.bank.index', 'Bank', 'admin.bank.*', 'bank'],
        ['admin.jenis-usaha.index', 'Jenis Usaha', 'admin.jenis-usaha.*', 'briefcase'],
        ['admin.jenis-pengeluaran.index', 'Jenis Pengeluaran', 'admin.jenis-pengeluaran.*', 'banknotes'],
        ['admin.kategori-produk.index', 'Kategori Produk', 'admin.kategori-produk.*', 'tag'],
    ];
@endphp

<x-app-shell
    brand="IBC Admin"
    :title="$title ?? ('Admin · '.$header)"
    :header="$header"
    :nav="$nav"
    sidebar="bg-slate-900 text-slate-300"
    brand-border="border-slate-800"
    link-hover="hover:bg-white/5 hover:text-white"
    link-active="bg-white/10 text-white"
    active-bar="border-indigo-400"
    accent="hover:text-indigo-600">
    {{ $slot }}
</x-app-shell>
