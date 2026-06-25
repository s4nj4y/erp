@php $u = $user ?? null; @endphp
<div>
    <label class="block text-sm font-medium mb-1">Nama</label>
    <input name="name" value="{{ old('name', $u?->name) }}" required
           class="w-full rounded-md border-gray-300 text-sm">
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Username</label>
        <input name="username" value="{{ old('username', $u?->username) }}"
               class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Email</label>
        <input type="email" name="email" value="{{ old('email', $u?->email) }}" required
               class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Role</label>
        <select name="role" class="w-full rounded-md border-gray-300 text-sm">
            @foreach (['admin', 'umkm', 'customer'] as $r)
                <option value="{{ $r }}" @selected(old('role', $u?->role) === $r)>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Status</label>
        <select name="status" class="w-full rounded-md border-gray-300 text-sm">
            <option value="1" @selected(old('status', $u?->status ?? 1) == 1)>Aktif</option>
            <option value="0" @selected(old('status', $u?->status ?? 1) == 0)>Nonaktif</option>
        </select>
    </div>
</div>
<div class="grid grid-cols-2 gap-4">
    <div>
        <label class="block text-sm font-medium mb-1">Password</label>
        <input type="password" name="password" {{ $u ? '' : 'required' }}
               class="w-full rounded-md border-gray-300 text-sm">
    </div>
    <div>
        <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
        <input type="password" name="password_confirmation"
               class="w-full rounded-md border-gray-300 text-sm">
    </div>
</div>
