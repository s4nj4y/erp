<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Transaksi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TransaksiController extends Controller
{
    public function index(Request $request): View
    {
        $transaksi = Transaksi::with('umkm')
            ->withCount('detail')
            ->where('customer_id', $request->user()->id)
            ->latest('tanggal')
            ->paginate(10);

        return view('customer.transaksi.index', compact('transaksi'));
    }

    public function show(Request $request, Transaksi $transaksi): View
    {
        $this->authorize('viewAsCustomer', $transaksi);
        $transaksi->load('umkm.rekening.bank', 'bank', 'detail.produk');
        $total = $transaksi->detail->sum(fn ($d) => $d->harga * $d->qty);

        return view('customer.transaksi.show', compact('transaksi', 'total'));
    }

    public function uploadBukti(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorize('viewAsCustomer', $transaksi);
        $request->validate(['bukti_pembayaran' => 'required|image|max:2048']);

        if ($transaksi->bukti_pembayaran) {
            Storage::disk('public')->delete($transaksi->bukti_pembayaran);
        }
        $transaksi->update([
            'bukti_pembayaran' => $request->file('bukti_pembayaran')->store('bukti', 'public'),
            'status_bayar' => 'menunggu_verifikasi',
        ]);

        return back()->with('success', 'Bukti pembayaran diunggah. Menunggu verifikasi UMKM.');
    }

    public function terima(Request $request, Transaksi $transaksi): RedirectResponse
    {
        $this->authorize('viewAsCustomer', $transaksi);
        $transaksi->update(['status' => 'selesai']);

        return back()->with('success', 'Pesanan ditandai diterima.');
    }

    public function invoice(Request $request, Transaksi $transaksi): View
    {
        $this->authorize('viewAsCustomer', $transaksi);
        $transaksi->load('umkm', 'bank', 'detail.produk', 'customer');
        $total = $transaksi->detail->sum(fn ($d) => $d->harga * $d->qty);

        return view('customer.transaksi.invoice', compact('transaksi', 'total'));
    }
}
