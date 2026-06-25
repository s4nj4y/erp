<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    use ResolvesUmkm;

    public function store(Request $request, Produk $produk): RedirectResponse
    {
        abort_unless($produk->umkm_id === $this->umkm($request)?->id, 403);

        $data = $request->validate([
            'status' => 'required|in:masuk,keluar',
            'jumlah' => 'required|integer|min:1',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string',
        ]);

        $masuk = $data['status'] === 'masuk';
        if (! $masuk && $data['jumlah'] > $produk->stok) {
            return back()->with('success', 'Jumlah keluar melebihi stok.');
        }

        DB::transaction(function () use ($produk, $data, $masuk) {
            Stok::create([
                'produk_id' => $produk->id,
                'status' => $data['status'],
                'jumlah_masuk' => $masuk ? $data['jumlah'] : 0,
                'jumlah_keluar' => $masuk ? 0 : $data['jumlah'],
                'tanggal' => $data['tanggal'],
                'keterangan' => $data['keterangan'] ?? null,
            ]);
            $produk->increment('stok', $masuk ? $data['jumlah'] : -$data['jumlah']);
        });

        return back()->with('success', 'Pergerakan stok dicatat.');
    }

    public function destroy(Request $request, Stok $stok): RedirectResponse
    {
        abort_unless($stok->produk?->umkm_id === $this->umkm($request)?->id, 403);

        DB::transaction(function () use ($stok) {
            $delta = $stok->jumlah_masuk - $stok->jumlah_keluar;
            $stok->produk?->decrement('stok', $delta);
            $stok->delete();
        });

        return back()->with('success', 'Catatan stok dihapus.');
    }
}
