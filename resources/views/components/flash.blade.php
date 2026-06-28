{{-- Notifikasi sukses (auto-dismiss 5s) & ringkasan error. Dipakai di semua layout. --}}
@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
         x-transition.opacity.duration.300ms role="status" aria-live="polite"
         class="mb-4 flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 shadow-sm">
        <x-icon name="check" class="w-5 h-5 mt-0.5 text-green-600" />
        <p class="flex-1">{{ session('success') }}</p>
        <button type="button" @click="show = false" aria-label="Tutup" class="text-green-600 hover:text-green-800">
            <x-icon name="close" class="w-4 h-4" />
        </button>
    </div>
@endif

@if ($errors->any())
    <div role="alert" aria-live="assertive"
         class="mb-4 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
        <x-icon name="warning" class="w-5 h-5 mt-0.5 text-red-600" />
        <div class="flex-1">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    </div>
@endif
