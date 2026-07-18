<?php

namespace Tests\Feature\Blackbox;

use App\Models\JenisUsaha;
use App\Models\Umkm;
use App\Models\User;

/**
 * Teknik 3 — Decision Table (DT-01..DT-08).
 * Kombinasi kondisi: aktor (pemilik / UMKM lain / customer) x status pembayaran/pesanan x aksi.
 */
class DecisionTableTest extends BlackboxTestCase
{
    /** DT-01: pemilik + menunggu_verifikasi + verifikasi -> terverifikasi & diproses. */
    public function test_dt01_pemilik_verifikasi_saat_menunggu(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertSessionHasNoErrors();

        $trx->refresh();
        $this->assertSame(['terverifikasi', 'diproses'], [$trx->status_bayar, $trx->status]);
    }

    /** DT-02: pemilik + belum bayar + verifikasi -> ditolak guard. */
    public function test_dt02_verifikasi_saat_belum_bayar_ditolak(): void
    {
        $trx = $this->transaksiPadaState('pending', 'belum');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertSessionHasErrors('status_bayar');

        $this->assertSame('belum', $trx->fresh()->status_bayar);
    }

    /** DT-03: pemilik UMKM lain + menunggu_verifikasi + verifikasi -> 404 (kepemilikan). */
    public function test_dt03_umkm_lain_verifikasi_404(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi');
        $pemilikLain = User::factory()->create(['role' => 'umkm']);
        Umkm::create(['user_id' => $pemilikLain->id, 'jenis_usaha_id' => JenisUsaha::first()->id, 'nama_umkm' => 'Toko Lain', 'status' => true]);

        $this->actingAs($pemilikLain)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertNotFound();

        $this->assertSame('menunggu_verifikasi', $trx->fresh()->status_bayar);
    }

    /** DT-04: pemilik + menunggu_verifikasi + tolak -> dibatalkan & stok kembali. */
    public function test_dt04_pemilik_tolak_saat_menunggu_stok_kembali(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi'); // stok 5 -> 3

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.tolak', $trx))
            ->assertSessionHasNoErrors();

        $trx->refresh();
        $this->assertSame(['ditolak', 'dibatalkan'], [$trx->status_bayar, $trx->status]);
        $this->assertSame(5, $this->produk->fresh()->stok);
    }

    /** DT-05: pemilik + sudah terverifikasi + tolak -> ditolak guard. */
    public function test_dt05_tolak_setelah_terverifikasi_ditolak(): void
    {
        $trx = $this->transaksiPadaState('diproses', 'terverifikasi');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.tolak', $trx))
            ->assertSessionHasErrors('status_bayar');

        $this->assertSame('terverifikasi', $trx->fresh()->status_bayar);
    }

    /** DT-06: pemilik + status diproses + kirim -> dikirim. */
    public function test_dt06_kirim_saat_diproses(): void
    {
        $trx = $this->transaksiPadaState('diproses', 'terverifikasi');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.kirim', $trx))
            ->assertSessionHasNoErrors();

        $this->assertSame('dikirim', $trx->fresh()->status);
    }

    /** DT-07: pemilik + status pending + kirim -> ditolak guard. */
    public function test_dt07_kirim_saat_pending_ditolak(): void
    {
        $trx = $this->transaksiPadaState('pending', 'belum');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.kirim', $trx))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $trx->fresh()->status);
    }

    /** DT-08: customer (peran salah) + verifikasi -> 403. */
    public function test_dt08_customer_verifikasi_403(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi');

        $this->actingAs($this->customer)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertForbidden();

        $this->assertSame('menunggu_verifikasi', $trx->fresh()->status_bayar);
    }
}
