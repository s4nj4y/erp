<?php

namespace Tests\Feature\Api;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmTransaksiTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;
    private Umkm $umkm;
    private Produk $produk;
    private Transaksi $trx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);
        $this->produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 3, 'show' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $this->trx = Transaksi::create([
            'customer_id' => $customer->id, 'umkm_id' => $this->umkm->id,
            'kode_transaksi' => 'INV-T3', 'tanggal' => now(),
            'status' => 'pending', 'status_bayar' => 'menunggu_verifikasi',
        ]);
        $this->trx->detail()->create(['produk_id' => $this->produk->id, 'qty' => 2, 'harga' => 10000]);
    }

    public function test_daftar_dengan_filter_status_bayar(): void
    {
        Transaksi::create([
            'customer_id' => $this->trx->customer_id, 'umkm_id' => $this->umkm->id,
            'kode_transaksi' => 'INV-T3B', 'tanggal' => now(), 'status_bayar' => 'belum',
        ]);

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/transaksi?status=menunggu_verifikasi');

        $res->assertOk();
        $this->assertCount(1, $res->json('data.data'));
        $this->assertSame('INV-T3', $res->json('data.data.0.kode_transaksi'));
    }

    public function test_transaksi_umkm_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'umkm']);
        Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);

        $this->actingAs($lain, 'sanctum')->getJson("/api/umkm/transaksi/{$this->trx->id}")->assertNotFound();
    }

    public function test_detail_dengan_total(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson("/api/umkm/transaksi/{$this->trx->id}");

        $res->assertOk()->assertJsonPath('data.total', 20000);
    }

    public function test_verifikasi_pembayaran(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')
            ->postJson("/api/umkm/transaksi/{$this->trx->id}/verifikasi")->assertOk();

        $this->trx->refresh();
        $this->assertSame('terverifikasi', $this->trx->status_bayar);
        $this->assertSame('diproses', $this->trx->status);
    }

    public function test_tolak_mengembalikan_stok(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')
            ->postJson("/api/umkm/transaksi/{$this->trx->id}/tolak")->assertOk();

        $this->trx->refresh();
        $this->assertSame('ditolak', $this->trx->status_bayar);
        $this->assertSame('dibatalkan', $this->trx->status);
        $this->assertSame(5, $this->produk->fresh()->stok); // 3 + 2 dikembalikan
        $this->assertDatabaseHas('stok', ['produk_id' => $this->produk->id, 'status' => 'masuk', 'jumlah_masuk' => 2]);
    }

    public function test_kirim(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')
            ->postJson("/api/umkm/transaksi/{$this->trx->id}/kirim")->assertOk();

        $this->assertSame('dikirim', $this->trx->fresh()->status);
    }
}
