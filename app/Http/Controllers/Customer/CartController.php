<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = KeranjangBelanja::with('produk.umkm')
            ->where('user_id', $request->user()->id)
            ->get();

        // Kelompokkan per UMKM untuk checkout terpisah
        $grouped = $items->groupBy(fn ($i) => $i->produk?->umkm_id);

        return view('customer.cart', compact('items', 'grouped'));
    }

    public function store(Request $request, Produk $produk): RedirectResponse
    {
        $qty = max(1, (int) $request->input('qty', 1));

        if (! $produk->show || $produk->stok < 1) {
            return back()->with('success', 'Produk tidak tersedia.');
        }

        $item = KeranjangBelanja::firstOrNew([
            'user_id' => $request->user()->id,
            'produk_id' => $produk->id,
        ]);
        $item->qty = min($produk->stok, ($item->qty ?? 0) + $qty);
        $item->save();

        return back()->with('success', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, KeranjangBelanja $keranjang): RedirectResponse
    {
        $this->authorizeOwner($request, $keranjang);

        $action = $request->input('action');
        if ($action === 'increase') {
            $keranjang->qty = min($keranjang->produk->stok, $keranjang->qty + 1);
        } elseif ($action === 'decrease') {
            $keranjang->qty = max(1, $keranjang->qty - 1);
        } else {
            $keranjang->qty = max(1, min($keranjang->produk->stok, (int) $request->input('qty', $keranjang->qty)));
        }
        $keranjang->save();

        return back();
    }

    public function destroy(Request $request, KeranjangBelanja $keranjang): RedirectResponse
    {
        $this->authorizeOwner($request, $keranjang);
        $keranjang->delete();

        return back()->with('success', 'Item dihapus dari keranjang.');
    }

    private function authorizeOwner(Request $request, KeranjangBelanja $keranjang): void
    {
        abort_unless($keranjang->user_id === $request->user()->id, 403);
    }
}
