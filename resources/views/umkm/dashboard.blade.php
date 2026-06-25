<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard UMKM</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @unless ($umkm)
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 p-4 rounded-lg mb-6 text-sm">
                    Profil UMKM Anda belum dilengkapi.
                </div>
            @else
                <div class="bg-white p-6 rounded-lg shadow-sm mb-6">
                    <div class="text-lg font-semibold">{{ $umkm->nama_umkm }}</div>
                    <div class="text-sm text-gray-500">{{ $umkm->alamat }}</div>
                </div>
            @endunless

            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['produk'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Produk</div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-indigo-600">{{ $stats['transaksi'] }}</div>
                    <div class="text-sm text-gray-500 mt-1">Transaksi</div>
                </div>
            </div>

            <div class="bg-white mt-6 p-6 rounded-lg shadow-sm text-gray-600">
                <p class="font-medium mb-2">Modul UMKM (akan dibangun bertahap):</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>Profil &amp; Rekening</li>
                    <li>Produk, Stok, Stok Opname</li>
                    <li>Saldo, Pengeluaran, Keuangan</li>
                    <li>Transaksi &amp; Laporan (laba-rugi, pendapatan, dll)</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
