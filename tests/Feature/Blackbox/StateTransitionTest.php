<?php

namespace Tests\Feature\Blackbox;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Teknik 4 — State Transition (ST-01..ST-10).
 * Siklus hidup pesanan: pending/belum -> menunggu_verifikasi -> terverifikasi|ditolak
 * -> diproses -> dikirim -> selesai. Menguji transisi valid dan tak-valid.
 */
class StateTransitionTest extends BlackboxTestCase
{
    private function unggahBukti($trx)
    {
        return $this->actingAs($this->customer)
            ->post(route('transaksi.bukti', $trx), ['bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg')]);
    }

    /** ST-01: transisi valid — belum -> menunggu_verifikasi via unggah bukti. */
    public function test_st01_unggah_bukti_saat_belum(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState('pending', 'belum');

        $this->unggahBukti($trx)->assertSessionHasNoErrors();

        $this->assertSame('menunggu_verifikasi', $trx->fresh()->status_bayar);
    }

    /** ST-02: transisi valid — menunggu_verifikasi -> terverifikasi (pending -> diproses). */
    public function test_st02_verifikasi_saat_menunggu(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi');

        $this->actingAs($this->pemilik)->post(route('umkm.transaksi.verifikasi', $trx));

        $trx->refresh();
        $this->assertSame(['terverifikasi', 'diproses'], [$trx->status_bayar, $trx->status]);
    }

    /** ST-03: transisi valid — menunggu_verifikasi -> ditolak (pending -> dibatalkan). */
    public function test_st03_tolak_saat_menunggu(): void
    {
        $trx = $this->transaksiPadaState('pending', 'menunggu_verifikasi');

        $this->actingAs($this->pemilik)->post(route('umkm.transaksi.tolak', $trx));

        $trx->refresh();
        $this->assertSame(['ditolak', 'dibatalkan'], [$trx->status_bayar, $trx->status]);
    }

    /** ST-04: transisi valid — diproses -> dikirim. */
    public function test_st04_kirim_saat_diproses(): void
    {
        $trx = $this->transaksiPadaState('diproses', 'terverifikasi');

        $this->actingAs($this->pemilik)->post(route('umkm.transaksi.kirim', $trx));

        $this->assertSame('dikirim', $trx->fresh()->status);
    }

    /** ST-05: transisi valid — dikirim -> selesai via konfirmasi customer. */
    public function test_st05_terima_saat_dikirim(): void
    {
        $trx = $this->transaksiPadaState('dikirim', 'terverifikasi');

        $this->actingAs($this->customer)->post(route('transaksi.terima', $trx));

        $this->assertSame('selesai', $trx->fresh()->status);
    }

    /** ST-06: transisi tak-valid — verifikasi ulang saat sudah terverifikasi. */
    public function test_st06_verifikasi_ulang_ditolak(): void
    {
        $trx = $this->transaksiPadaState('diproses', 'terverifikasi');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertSessionHasErrors('status_bayar');

        $this->assertSame('diproses', $trx->fresh()->status);
    }

    /** ST-07: transisi tak-valid — kirim saat masih pending. */
    public function test_st07_kirim_saat_pending_ditolak(): void
    {
        $trx = $this->transaksiPadaState('pending', 'belum');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.kirim', $trx))
            ->assertSessionHasErrors('status');

        $this->assertSame('pending', $trx->fresh()->status);
    }

    /** ST-08: transisi tak-valid — customer menandai selesai saat pesanan masih pending. */
    public function test_st08_terima_saat_pending_ditolak(): void
    {
        $trx = $this->transaksiPadaState('pending', 'belum');

        $this->actingAs($this->customer)->post(route('transaksi.terima', $trx));

        // Spesifikasi: pesanan hanya boleh ditandai selesai setelah dikirim.
        $this->assertSame('pending', $trx->fresh()->status);
    }

    /** ST-09: transisi tak-valid — unggah ulang bukti setelah pembayaran terverifikasi. */
    public function test_st09_unggah_bukti_setelah_terverifikasi_ditolak(): void
    {
        Storage::fake('public');
        $trx = $this->transaksiPadaState('diproses', 'terverifikasi');

        $this->unggahBukti($trx);

        // Spesifikasi: verifikasi yang sudah diberikan tidak boleh ter-reset oleh unggahan baru.
        $this->assertSame('terverifikasi', $trx->fresh()->status_bayar);
    }

    /** ST-10: transisi tak-valid — tolak ulang saat sudah ditolak. */
    public function test_st10_tolak_ulang_ditolak(): void
    {
        $trx = $this->transaksiPadaState('dibatalkan', 'ditolak');

        $this->actingAs($this->pemilik)
            ->post(route('umkm.transaksi.tolak', $trx))
            ->assertSessionHasErrors('status_bayar');

        $this->assertSame('dibatalkan', $trx->fresh()->status);
    }
}
