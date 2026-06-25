<x-admin-layout header="Dashboard Admin">
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach (['umkm' => 'UMKM', 'produk' => 'Produk', 'customer' => 'Customer', 'transaksi' => 'Transaksi'] as $key => $label)
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <div class="text-3xl font-bold text-indigo-600">{{ $stats[$key] }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white mt-6 p-6 rounded-lg shadow-sm">
        <h2 class="font-semibold mb-3">Master Data</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-3 text-sm">
            <a href="{{ route('admin.users.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-indigo-400 hover:text-indigo-600">Users</a>
            <a href="{{ route('admin.bank.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-indigo-400 hover:text-indigo-600">Bank</a>
            <a href="{{ route('admin.jenis-usaha.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-indigo-400 hover:text-indigo-600">Jenis Usaha</a>
            <a href="{{ route('admin.jenis-pengeluaran.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-indigo-400 hover:text-indigo-600">Jenis Pengeluaran</a>
            <a href="{{ route('admin.kategori-produk.index') }}" class="px-4 py-3 rounded-md border border-gray-200 hover:border-indigo-400 hover:text-indigo-600">Kategori Produk</a>
        </div>
    </div>
</x-admin-layout>
