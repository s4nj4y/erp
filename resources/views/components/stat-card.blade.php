@props(['label' => '', 'value' => '', 'icon' => 'dot', 'tone' => 'emerald'])

@php
    $tones = [
        'emerald' => 'text-emerald-600 bg-emerald-50',
        'red'     => 'text-red-500 bg-red-50',
        'indigo'  => 'text-indigo-600 bg-indigo-50',
        'amber'   => 'text-amber-600 bg-amber-50',
    ];
    $t = $tones[$tone] ?? $tones['emerald'];
@endphp

<div class="card p-5">
    <div class="flex items-center justify-between gap-3">
        <div class="min-w-0">
            <div class="text-2xl font-bold text-gray-900 tabular-nums truncate">{{ $value }}</div>
            <div class="text-sm text-gray-500 mt-1">{{ $label }}</div>
        </div>
        <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-lg {{ $t }}">
            <x-icon :name="$icon" class="w-5 h-5" />
        </span>
    </div>
</div>
