<x-admin-layout header="Master · Users">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <form method="GET" class="flex flex-wrap gap-2">
            <label for="q" class="sr-only">Cari user</label>
            <input id="q" name="q" value="{{ request('q') }}" placeholder="Cari nama / email" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
            <select name="role" class="rounded-lg border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">Semua role</option>
                @foreach (['admin', 'umkm', 'customer'] as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200">Filter</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 transition-colors">
            <x-icon name="users" class="w-4 h-4" /> Tambah User
        </a>
    </div>

    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-left text-gray-500">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nama</th><th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Role</th><th class="px-4 py-3 font-medium">Status</th>
                        <th class="px-4 py-3 font-medium w-32 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $u)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                            <td class="px-4 py-3">
                                <span @class([
                                    'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset',
                                    'bg-purple-50 text-purple-700 ring-purple-200' => $u->role === 'admin',
                                    'bg-blue-50 text-blue-700 ring-blue-200' => $u->role === 'umkm',
                                    'bg-gray-100 text-gray-600 ring-gray-200' => $u->role === 'customer',
                                ])>{{ ucfirst($u->role) }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset {{ $u->status ? 'bg-green-50 text-green-700 ring-green-200' : 'bg-red-50 text-red-700 ring-red-200' }}">
                                    {{ $u->status ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('admin.users.edit', $u) }}" class="font-medium text-indigo-600 hover:text-indigo-700">Edit</a>
                                <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline" onsubmit="return confirm('Hapus user ini?')">@csrf @method('DELETE')<button class="ml-3 font-medium text-red-600 hover:text-red-700">Hapus</button></form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">
                            <x-icon name="users" class="w-9 h-9 mx-auto text-gray-300" />
                            <p class="mt-2">Tidak ada user.</p>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $users->links() }}</div>
    </div>
</x-admin-layout>
