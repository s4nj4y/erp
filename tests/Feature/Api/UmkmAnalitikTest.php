<?php

namespace Tests\Feature\Api;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmAnalitikTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;
    private Umkm $umkm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko A', 'status' => true]);

        $produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik',
            'harga_modal' => 5000, 'harga' => 10000, 'stok' => 5, 'show' => true]);

        // Pelanggan baru: transaksi pertamanya dalam periode (2 x 10000 = 20000)
        $baru = User::factory()->create(['role' => 'customer']);
        $this->penjualan($this->umkm->id, $baru->id, $produk->id, now(), 2, 'INV-A1');

        // Pelanggan lama: pernah beli 45 hari lalu, beli lagi dalam periode (1 x 10000)
        $lama = User::factory()->create(['role' => 'customer']);
        $this->penjualan($this->umkm->id, $lama->id, $produk->id, now()->subDays(45), 1, 'INV-A0');
        $this->penjualan($this->umkm->id, $lama->id, $produk->id, now(), 1, 'INV-A2');

        // Toko lain: tidak boleh bocor ke analitik Toko A
        $pemilikB = User::factory()->create(['role' => 'umkm']);
        $umkmB = Umkm::create(['user_id' => $pemilikB->id, 'nama_umkm' => 'Toko B', 'status' => true]);
        $produkB = Produk::create(['umkm_id' => $umkmB->id, 'nama_produk' => 'Kopi',
            'harga_modal' => 1000, 'harga' => 99000, 'stok' => 5, 'show' => true]);
        $this->penjualan($umkmB->id, $baru->id, $produkB->id, now(), 1, 'INV-B1');
    }

    private function penjualan(int $umkmId, int $customerId, int $produkId, $tanggal, int $qty, string $kode): void
    {
        $trx = Transaksi::create(['customer_id' => $customerId, 'umkm_id' => $umkmId,
            'kode_transaksi' => $kode, 'tanggal' => $tanggal,
            'status' => 'diproses', 'status_bayar' => 'terverifikasi']);
        $trx->detail()->create(['produk_id' => $produkId, 'qty' => $qty, 'harga' => 10000]);
    }

    public function test_tren_terisolasi_per_umkm(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/analitik/tren?periode=30d');

        // 20000 + 10000, transaksi Toko B (99000) tidak ikut
        $res->assertOk()
            ->assertJsonPath('data.total_omzet', 30000)
            ->assertJsonPath('data.total_transaksi', 2)
            ->assertJsonPath('data.periode', '30d');
        $this->assertCount(30, $res->json('data.labels'));
    }

    public function test_produk_terlaris(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/analitik/produk-terlaris');

        $res->assertOk()
            ->assertJsonCount(1, 'data.rows')
            ->assertJsonPath('data.rows.0.nama', 'Keripik')
            ->assertJsonPath('data.rows.0.terjual', 3)
            ->assertJsonPath('data.rows.0.nilai', 30000);
    }

    public function test_pelanggan_baru_vs_lama(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/analitik/pelanggan?periode=30d');

        $res->assertOk()
            ->assertJsonPath('data.pelanggan_baru', 1)
            ->assertJsonPath('data.pelanggan_lama', 1)
            ->assertJsonPath('data.top.0.belanja', 20000);
    }

    public function test_periode_tidak_valid_422(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')
            ->getJson('/api/umkm/analitik/tren?periode=99x')->assertStatus(422);
    }

    public function test_tanpa_profil_409(): void
    {
        $baru = User::factory()->create(['role' => 'umkm']);

        $this->actingAs($baru, 'sanctum')->getJson('/api/umkm/analitik/tren')->assertStatus(409);
    }
}
