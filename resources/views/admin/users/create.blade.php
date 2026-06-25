<x-admin-layout header="Tambah User">
    <div class="max-w-lg bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf
            @include('admin.users._fields', ['user' => null])
            <div class="flex gap-2 pt-2">
                <button class="bg-indigo-600 text-white rounded-md px-4 py-2 text-sm">Simpan</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
