<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use App\Models\Stok;
use App\Models\Transaksi;
use App\Models\Umkm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request, Umkm $umkm): View|RedirectResponse
    {
        $items = $this->cartItemsForUmkm($request, $umkm);

        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('success', 'Tidak ada item untuk UMKM ini.');
        }

        $umkm->load('rekening.bank');
        $total = $items->sum(fn ($i) => $i->produk->harga * $i->qty);

        return view('customer.checkout', compact('umkm', 'items', 'total'));
    }

    public function store(Request $request, Umkm $umkm): RedirectResponse
    {
        $items = $this->cartItemsForUmkm($request, $umkm);
        if ($items->isEmpty()) {
            return redirect()->route('cart.index')->with('success', 'Keranjang kosong.');
        }

        $data = $request->validate([
            'bank_id' => ['required', Rule::exists('rekening_bank', 'bank_id')->where('umkm_id', $umkm->id)],
        ]);

        try {
            $transaksi = DB::transaction(function () use ($request, $umkm, $items, $data) {
                $trx = Transaksi::create([
                    'customer_id' => $request->user()->id,
                    'umkm_id' => $umkm->id,
                    'bank_id' => $data['bank_id'],
                    'kode_transaksi' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6)),
                    'tanggal' => now(),
                    'status' => 'pending',
                    'status_bayar' => 'belum',
                ]);

                foreach ($items as $item) {
                    // Kunci baris produk agar cek stok & decrement konsisten walau ada checkout bersamaan.
                    $produk = Produk::whereKey($item->produk_id)->lockForUpdate()->first();
                    if ($item->qty > $produk->stok) {
                        throw ValidationException::withMessages(['stok' => "Stok '{$produk->nama_produk}' tidak mencukupi."]);
                    }

                    $trx->detail()->create([
                        'produk_id' => $item->produk_id,
                        'qty' => $item->qty,
                        'harga' => $produk->harga,
                    ]);

                    // Kurangi stok + catat pergerakan
                    $produk->decrement('stok', $item->qty);
                    Stok::create([
                        'produk_id' => $item->produk_id,
                        'status' => 'keluar',
                        'jumlah_masuk' => 0,
                        'jumlah_keluar' => $item->qty,
                        'tanggal' => now()->toDateString(),
                        'keterangan' => 'Pesanan '.$trx->kode_transaksi,
                    ]);

                    $item->delete();
                }

                return $trx;
            });
        } catch (ValidationException $e) {
            return back()->with('success', $e->validator->errors()->first('stok'));
        }

        return redirect()->route('transaksi.show', $transaksi)
            ->with('success', 'Pesanan dibuat. Silakan unggah bukti pembayaran.');
    }

    private function cartItemsForUmkm(Request $request, Umkm $umkm)
    {
        return KeranjangBelanja::with('produk')
            ->where('user_id', $request->user()->id)
            ->whereHas('produk', fn ($q) => $q->where('umkm_id', $umkm->id))
            ->get();
    }
}
