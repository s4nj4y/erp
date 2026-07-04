<?php

namespace App\Http\Controllers\Umkm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\Stok;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    use ResolvesUmkm;

    public function index(Request $request): View|RedirectResponse
    {
        $umkm = $this->umkm($request);
        if (! $umkm) {
            return redirect()->route('umkm.profil.edit')->with('success', 'Lengkapi profil UMKM dulu.');
        }

        $transaksi = Transaksi::with('customer')
            ->withCount('detail')
            ->forUmkm($umkm)
            ->when($request->status, fn ($q) => $q->where('status_bayar', $request->status))
            ->latest('tanggal')
            ->paginate(10)
            ->withQueryString();

        return view('umkm.transaksi.index', compact('transaksi'));
    }

    public function show(Request $request, Transaksi $transaksi): View
    {
        $this->authorize('viewAsUmkm', $transaksi);
        $transaksi->load('customer', 'bank', 'detail.produk');
        $total = $transaksi->detail->sum(fn ($d) => $d->harga * $d->qty);

        return view('umkm.transaksi.show', compact('transaksi', 'total'));
    }

    /** Verifikasi pembayaran -> pesanan diproses. */
    public function verifikasi(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorize('manage', $transaksi);

        $boleh = DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehVerifikasi()) {
                return false;
            }
            $terkunci->update(['status_bayar' => 'terverifikasi', 'status' => 'diproses']);

            return true;
        });

        if (! $boleh) {
            return back()->withErrors(['status_bayar' => 'Pembayaran sudah diproses sebelumnya.']);
        }

        return back()->with('success', 'Pembayaran diverifikasi. Pesanan diproses.');
    }

    /** Tolak pembayaran -> kembalikan stok produk. */
    public function tolak(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorize('manage', $transaksi);

        $boleh = DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehTolak()) {
                return false;
            }

            $terkunci->loadMissing('detail.produk');
            foreach ($terkunci->detail as $d) {
                if ($d->produk) {
                    $d->produk->increment('stok', $d->qty);
                    Stok::create([
                        'produk_id' => $d->produk_id,
                        'status' => 'masuk',
                        'jumlah_masuk' => $d->qty,
                        'jumlah_keluar' => 0,
                        'tanggal' => now()->toDateString(),
                        'keterangan' => 'Pembatalan '.$terkunci->kode_transaksi,
                    ]);
                }
            }
            $terkunci->update(['status_bayar' => 'ditolak', 'status' => 'dibatalkan']);

            return true;
        });

        if (! $boleh) {
            return back()->withErrors(['status_bayar' => 'Pembayaran sudah diproses sebelumnya.']);
        }

        return back()->with('success', 'Pembayaran ditolak. Stok dikembalikan.');
    }

    /** Tandai pesanan dikirim. */
    public function kirim(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorize('manage', $transaksi);

        $boleh = DB::transaction(function () use ($transaksi) {
            $terkunci = Transaksi::whereKey($transaksi->id)->lockForUpdate()->first();
            if (! $terkunci->bolehKirim()) {
                return false;
            }
            $terkunci->update(['status' => 'dikirim']);

            return true;
        });

        if (! $boleh) {
            return back()->withErrors(['status' => 'Pesanan belum siap dikirim.']);
        }

        return back()->with('success', 'Pesanan ditandai dikirim.');
    }
}
