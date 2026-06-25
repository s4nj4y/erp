<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach (['umkm' => 'UMKM', 'produk' => 'Produk', 'customer' => 'Customer', 'transaksi' => 'Transaksi'] as $key => $label)
                    <div class="bg-white p-6 rounded-lg shadow-sm">
                        <div class="text-3xl font-bold text-indigo-600">{{ $stats[$key] }}</div>
                        <div class="text-sm text-gray-500 mt-1">{{ $label }}</div>
                    </div>
                @endforeach
            </div>

            <div class="bg-white mt-6 p-6 rounded-lg shadow-sm text-gray-600">
                <p class="font-medium mb-2">Modul admin (akan dibangun bertahap):</p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>Master: Users, Bank, Jenis Usaha, Jenis Pengeluaran, Kategori Produk</li>
                    <li>UMKM &amp; Rekening</li>
                    <li>Produk &amp; Stok</li>
                    <li>Transaksi masuk/keluar, Laba-rugi, Pendapatan</li>
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
