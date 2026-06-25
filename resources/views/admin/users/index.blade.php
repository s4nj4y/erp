<x-admin-layout header="Master · Users">
    <div class="flex items-center justify-between mb-4">
        <form method="GET" class="flex gap-2">
            <input name="q" value="{{ request('q') }}" placeholder="Cari nama / email"
                   class="rounded-md border-gray-300 text-sm">
            <select name="role" class="rounded-md border-gray-300 text-sm">
                <option value="">Semua role</option>
                @foreach (['admin', 'umkm', 'customer'] as $r)
                    <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
                @endforeach
            </select>
            <button class="px-4 py-2 bg-gray-200 rounded-md text-sm">Filter</button>
        </form>
        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm">+ Tambah User</a>
    </div>

    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 w-32 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($users as $u)
                    <tr>
                        <td class="px-4 py-3">{{ $u->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $u->email }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs
                                @class([
                                    'bg-purple-100 text-purple-700' => $u->role === 'admin',
                                    'bg-blue-100 text-blue-700' => $u->role === 'umkm',
                                    'bg-gray-100 text-gray-600' => $u->role === 'customer',
                                ])">{{ ucfirst($u->role) }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($u->status)
                                <span class="text-green-600">Aktif</span>
                            @else
                                <span class="text-red-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-indigo-600 hover:underline">Edit</a>
                            <form action="{{ route('admin.users.destroy', $u) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus user ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline ml-2">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-6 text-center text-gray-400">Tidak ada user.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4">{{ $users->links() }}</div>
    </div>
</x-admin-layout>
