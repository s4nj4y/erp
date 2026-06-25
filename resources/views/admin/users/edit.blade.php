<x-admin-layout header="Edit User">
    <div class="max-w-lg bg-white p-6 rounded-lg shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')
            @include('admin.users._fields', ['user' => $user])
            <p class="text-xs text-gray-400">Kosongkan password jika tidak ingin mengubahnya.</p>
            <div class="flex gap-2 pt-2">
                <button class="bg-indigo-600 text-white rounded-md px-4 py-2 text-sm">Perbarui</button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-gray-600">Batal</a>
            </div>
        </form>
    </div>
</x-admin-layout>
