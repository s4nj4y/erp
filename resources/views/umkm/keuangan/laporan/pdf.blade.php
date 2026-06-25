<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #222; }
        h1 { font-size: 16px; margin: 0; }
        .muted { color: #666; font-size: 11px; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f1f1f1; }
        .num { text-align: right; }
        .summary { margin-top: 14px; width: 50%; }
        .summary td { border: none; padding: 3px 0; }
        .summary .label { color: #555; }
        .summary .val { text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="muted">{{ $umkm->nama_umkm ?? '' }} &middot; Periode: {{ $periode }}</div>

    <table>
        <thead>
            <tr>@foreach ($headings as $h)<th class="{{ $loop->index > 0 ? 'num' : '' }}">{{ $h }}</th>@endforeach</tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>@foreach (array_values($r) as $i => $c)<td class="{{ $i > 0 ? 'num' : '' }}">{{ $c }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headings) }}">Tidak ada data.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if (! empty($summary))
        <table class="summary">
            @foreach ($summary as $label => $value)
                <tr><td class="label">{{ $label }}</td><td class="val">{{ $value }}</td></tr>
            @endforeach
        </table>
    @endif
</body>
</html>
