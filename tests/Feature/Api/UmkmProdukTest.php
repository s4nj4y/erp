<?php

namespace Tests\Feature\Api;

use App\Models\Produk;
use App\Models\Stok;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmProdukTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;
    private Umkm $umkm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);
    }

    private function buatProduk(array $attrs = []): Produk
    {
        return Produk::create(array_merge([
            'umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik',
            'harga_modal' => 5000, 'harga' => 10000, 'stok' => 5, 'show' => true,
        ], $attrs));
    }

    public function test_daftar_produk_hanya_milik_sendiri_dengan_pencarian(): void
    {
        $this->buatProduk();
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);
        Produk::create(['umkm_id' => $umkmLain->id, 'nama_produk' => 'Rahasia', 'harga' => 1, 'stok' => 1, 'show' => true]);

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/produk');

        $res->assertOk();
        $this->assertCount(1, $res->json('data.data'));
        $this->assertSame(5000, $res->json('data.data.0.harga_modal'));

        $this->assertCount(0, $this->actingAs($this->pemilik, 'sanctum')
            ->getJson('/api/umkm/produk?q=Rahasia')->json('data.data'));
    }

    public function test_tambah_produk(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/produk', [
            'nama_produk' => 'Sambal', 'harga_modal' => 3000, 'harga' => 8000, 'stok' => 10, 'show' => true,
        ]);

        $res->assertCreated()->assertJsonPath('data.nama_produk', 'Sambal')->assertJsonPath('data.harga_modal', 3000);
        $this->assertDatabaseHas('produk', ['nama_produk' => 'Sambal', 'umkm_id' => $this->umkm->id]);
    }

    public function test_update_dan_toggle_dan_hapus(): void
    {
        $p = $this->buatProduk();

        $this->actingAs($this->pemilik, 'sanctum')->putJson("/api/umkm/produk/{$p->id}", [
            'nama_produk' => 'Keripik Baru', 'harga_modal' => 5000, 'harga' => 12000, 'stok' => 5,
        ])->assertOk()->assertJsonPath('data.nama_produk', 'Keripik Baru');

        $this->actingAs($this->pemilik, 'sanctum')->patchJson("/api/umkm/produk/{$p->id}/toggle")
            ->assertOk()->assertJsonPath('data.show', false);

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/produk/{$p->id}")->assertOk();
        $this->assertDatabaseCount('produk', 0);
    }

    public function test_produk_umkm_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);
        $p = Produk::create(['umkm_id' => $umkmLain->id, 'nama_produk' => 'X', 'harga' => 1, 'stok' => 1, 'show' => true]);

        $this->actingAs($this->pemilik, 'sanctum')->putJson("/api/umkm/produk/{$p->id}", [
            'nama_produk' => 'Y', 'harga_modal' => 0, 'harga' => 1, 'stok' => 1,
        ])->assertNotFound();
    }

    public function test_stok_masuk_dan_keluar(): void
    {
        $p = $this->buatProduk(['stok' => 5]);

        $this->actingAs($this->pemilik, 'sanctum')->postJson("/api/umkm/produk/{$p->id}/stok", [
            'status' => 'masuk', 'jumlah' => 3, 'tanggal' => '2026-07-04',
        ])->assertCreated();
        $this->assertSame(8, $p->fresh()->stok);

        $this->actingAs($this->pemilik, 'sanctum')->postJson("/api/umkm/produk/{$p->id}/stok", [
            'status' => 'keluar', 'jumlah' => 2, 'tanggal' => '2026-07-04',
        ])->assertCreated();
        $this->assertSame(6, $p->fresh()->stok);
    }

    public function test_stok_keluar_melebihi_stok_422(): void
    {
        $p = $this->buatProduk(['stok' => 2]);

        $this->actingAs($this->pemilik, 'sanctum')->postJson("/api/umkm/produk/{$p->id}/stok", [
            'status' => 'keluar', 'jumlah' => 5, 'tanggal' => '2026-07-04',
        ])->assertUnprocessable();
        $this->assertSame(2, $p->fresh()->stok);
    }

    public function test_hapus_catatan_stok_mengembalikan_saldo_stok(): void
    {
        $p = $this->buatProduk(['stok' => 5]);
        $this->actingAs($this->pemilik, 'sanctum')->postJson("/api/umkm/produk/{$p->id}/stok", [
            'status' => 'masuk', 'jumlah' => 3, 'tanggal' => '2026-07-04',
        ]);
        $stok = Stok::latest('id')->first();

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/stok/{$stok->id}")->assertOk();
        $this->assertSame(5, $p->fresh()->stok);
    }
}
