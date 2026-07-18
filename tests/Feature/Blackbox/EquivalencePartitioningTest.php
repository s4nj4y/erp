<?php

namespace Tests\Feature\Blackbox;

use App\Models\Bank;
use App\Models\Transaksi;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Teknik 1 — Equivalence Partitioning (EP-01..EP-09).
 * Partisi input checkout, keranjang, dan unggah bukti pembayaran.
 */
class EquivalencePartitioningTest extends BlackboxTestCase
{
    /** EP-01: partisi valid — bank_id milik rekening UMKM. */
    public function test_ep01_bank_id_valid_membuat_pesanan(): void
    {
        $this->isiKeranjang();

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $this->bank->id])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('transaksi', ['umkm_id' => $this->umkm->id, 'status' => 'pending', 'status_bayar' => 'belum']);
    }

    /** EP-02: partisi tak valid — bank terdaftar tetapi bukan rekening UMKM ini. */
    public function test_ep02_bank_id_bukan_rekening_umkm_ditolak(): void
    {
        $this->isiKeranjang();
        $bankLain = Bank::create(['nama_bank' => 'BCA']);

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $bankLain->id])
            ->assertSessionHasErrors('bank_id');

        $this->assertDatabaseCount('transaksi', 0);
    }

    /** EP-03: partisi tak valid — bank_id kosong. */
    public function test_ep03_bank_id_kosong_ditolak(): void
    {
        $this->isiKeranjang();

        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), [])
            ->assertSessionHasErrors('bank_id');

        $this->assertDatabaseCount('transaksi', 0);
    }

    /** EP-04: partisi valid — qty dalam rentang 1..stok. */
    public function test_ep04_qty_valid_masuk_keranjang(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->produk), ['qty' => 3]);

        $this->assertDatabaseHas('keranjang_belanja', ['user_id' => $this->customer->id, 'qty' => 3]);
    }

    /** EP-05: partisi tak valid — qty melebihi stok, dibatasi ke stok. */
    public function test_ep05_qty_melebihi_stok_dibatasi(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->produk), ['qty' => 8]);

        $this->assertDatabaseHas('keranjang_belanja', ['user_id' => $this->customer->id, 'qty' => 5]);
    }

    /** EP-06: partisi tak valid — qty nol/negatif dinormalisasi ke 1. */
    public function test_ep06_qty_nol_dinormalisasi_satu(): void
    {
        $this->actingAs($this->customer)
            ->post(route('cart.store', $this->produk), ['qty' => 0]);

        $this->assertDatabaseHas('keranjang_belanja', ['user_id' => $this->customer->id, 'qty' => 1]);
    }

    /** EP-07: partisi valid — berkas bukti berupa citra. */
    public function test_ep07_bukti_citra_diterima(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState();

        $this->actingAs($this->customer)
            ->post(route('transaksi.bukti', $trx), ['bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg')])
            ->assertSessionHasNoErrors();

        $this->assertSame('menunggu_verifikasi', $trx->fresh()->status_bayar);
    }

    /** EP-08: partisi tak valid — berkas bukan citra. */
    public function test_ep08_bukti_bukan_citra_ditolak(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState();

        $this->actingAs($this->customer)
            ->post(route('transaksi.bukti', $trx), ['bukti_pembayaran' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('bukti_pembayaran');

        $this->assertSame('belum', $trx->fresh()->status_bayar);
    }

    /** EP-09: partisi tak valid — keranjang kosong saat checkout. */
    public function test_ep09_keranjang_kosong_dialihkan(): void
    {
        $this->actingAs($this->customer)
            ->post(route('checkout.store', $this->umkm), ['bank_id' => $this->bank->id])
            ->assertRedirect(route('cart.index'));

        $this->assertSame(0, Transaksi::count());
    }
}
