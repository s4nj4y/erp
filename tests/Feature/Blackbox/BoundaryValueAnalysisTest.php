<?php

namespace Tests\Feature\Blackbox;

use App\Models\KeranjangBelanja;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Teknik 2 — Boundary Value Analysis (BVA-01..BVA-08).
 * Batas qty terhadap stok (5), batas bawah qty (1), dan batas ukuran berkas (2048 KB).
 */
class BoundaryValueAnalysisTest extends BlackboxTestCase
{
    /** BVA-01: qty = stok (batas atas tepat) — checkout sukses, stok jadi 0. */
    public function test_bva01_qty_sama_dengan_stok_sukses(): void
    {
        $this->isiKeranjang(5);

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $this->bank->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(0, $this->produk->fresh()->stok);
    }

    /** BVA-02: qty = stok + 1 — checkout gagal tanpa efek samping. */
    public function test_bva02_qty_stok_plus_satu_gagal(): void
    {
        // Qty di keranjang dibuat 6 langsung (mensimulasikan stok berkurang setelah item masuk keranjang).
        KeranjangBelanja::create(['user_id' => $this->customer->id, 'produk_id' => $this->produk->id, 'qty' => 6]);

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $this->bank->id]);

        $this->assertDatabaseCount('transaksi', 0);
        $this->assertSame(5, $this->produk->fresh()->stok);
    }

    /** BVA-03: qty = 1 (batas bawah) — checkout sukses, stok jadi 4. */
    public function test_bva03_qty_satu_sukses(): void
    {
        $this->isiKeranjang(1);

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $this->bank->id])
            ->assertSessionHasNoErrors();

        $this->assertSame(4, $this->produk->fresh()->stok);
    }

    /** BVA-04: qty = 0 (di bawah batas bawah) saat tambah keranjang — dinormalisasi ke 1. */
    public function test_bva04_qty_nol_menjadi_satu(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->produk), ['qty' => 0]);

        $this->assertDatabaseHas('keranjang_belanja', ['qty' => 1]);
    }

    /** BVA-05: decrease pada qty = 1 — tetap 1 (tidak menembus batas bawah). */
    public function test_bva05_decrease_pada_qty_satu_tetap_satu(): void
    {
        $item = $this->isiKeranjang(1);

        $this->actingAs($this->customer)
            ->patch(route('cart.update', $item), ['action' => 'decrease']);

        $this->assertSame(1, $item->fresh()->qty);
    }

    /** BVA-06: increase pada qty = stok — tetap stok (tidak menembus batas atas). */
    public function test_bva06_increase_pada_qty_stok_tetap_stok(): void
    {
        $item = $this->isiKeranjang(5);

        $this->actingAs($this->customer)
            ->patch(route('cart.update', $item), ['action' => 'increase']);

        $this->assertSame(5, $item->fresh()->qty);
    }

    /** BVA-07: ukuran berkas = 2048 KB (batas tepat) — diterima. */
    public function test_bva07_berkas_2048kb_diterima(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState();

        $this->actingAs($this->customer)
            ->post(route('transaksi.bukti', $trx), ['bukti_pembayaran' => UploadedFile::fake()->create('bukti.jpg', 2048, 'image/jpeg')])
            ->assertSessionHasNoErrors();

        $this->assertSame('menunggu_verifikasi', $trx->fresh()->status_bayar);
    }

    /** BVA-08: ukuran berkas = 2049 KB (batas + 1) — ditolak. */
    public function test_bva08_berkas_2049kb_ditolak(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState();

        $this->actingAs($this->customer)
            ->post(route('transaksi.bukti', $trx), ['bukti_pembayaran' => UploadedFile::fake()->create('bukti.jpg', 2049, 'image/jpeg')])
            ->assertSessionHasErrors('bukti_pembayaran');

        $this->assertSame('belum', $trx->fresh()->status_bayar);
    }
}
