@props(['status' => ''])

@php
    // Warna semantik per status; teks tetap ada (tidak mengandalkan warna saja).
    $map = [
        'pending'             => 'bg-amber-50 text-amber-700 ring-amber-200',
        'diproses'            => 'bg-blue-50 text-blue-700 ring-blue-200',
        'dikirim'             => 'bg-indigo-50 text-indigo-700 ring-indigo-200',
        'selesai'             => 'bg-green-50 text-green-700 ring-green-200',
        'dibatalkan'          => 'bg-red-50 text-red-700 ring-red-200',
        'belum'               => 'bg-gray-100 text-gray-600 ring-gray-200',
        'menunggu_verifikasi' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'terverifikasi'       => 'bg-green-50 text-green-700 ring-green-200',
        'ditolak'             => 'bg-red-50 text-red-700 ring-red-200',
    ];
    $cls = $map[$status] ?? 'bg-gray-100 text-gray-600 ring-gray-200';
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset whitespace-nowrap {{ $cls }}">
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
