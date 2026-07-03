<?php

namespace Tests\Feature\Api;

use App\Models\JenisUsaha;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeranjangTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Produk $produk;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
        $pemilik = User::factory()->create(['role' => 'umkm']);
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        $umkm = Umkm::create(['user_id' => $pemilik->id, 'jenis_usaha_id' => $jenis->id, 'nama_umkm' => 'Toko Maju', 'status' => true]);
        $this->produk = Produk::create(['umkm_id' => $umkm->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true]);
    }

    public function test_butuh_login_dan_role_customer(): void
    {
        $this->getJson('/api/keranjang')->assertUnauthorized();

        $umkmUser = User::factory()->create(['role' => 'umkm']);
        $this->actingAs($umkmUser, 'sanctum')->getJson('/api/keranjang')->assertForbidden();
    }

    public function test_tambah_item_dan_lihat_keranjang(): void
    {
        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/keranjang/{$this->produk->id}", ['qty' => 2])
            ->assertCreated();

        $res = $this->actingAs($this->customer, 'sanctum')->getJson('/api/keranjang');
        $res->assertOk()
            ->assertJsonPath('data.0.qty', 2)
            ->assertJsonPath('data.0.produk.nama_produk', 'Keripik')
            ->assertJsonPath('data.0.produk.umkm.nama_umkm', 'Toko Maju');
    }

    public function test_tambah_item_sama_menambah_qty_dengan_clamp_stok(): void
    {
        $this->actingAs($this->customer, 'sanctum')->postJson("/api/keranjang/{$this->produk->id}", ['qty' => 4]);
        $this->actingAs($this->customer, 'sanctum')->postJson("/api/keranjang/{$this->produk->id}", ['qty' => 4]);

        $this->assertSame(5, KeranjangBelanja::first()->qty); // clamp ke stok
    }

    public function test_produk_habis_atau_hidden_ditolak_422(): void
    {
        $this->produk->update(['stok' => 0]);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/keranjang/{$this->produk->id}")
            ->assertUnprocessable();
    }

    public function test_update_qty_increase_decrease(): void
    {
        $item = KeranjangBelanja::create(['user_id' => $this->customer->id, 'produk_id' => $this->produk->id, 'qty' => 2]);

        $this->actingAs($this->customer, 'sanctum')
            ->patchJson("/api/keranjang/{$item->id}", ['action' => 'increase'])
            ->assertOk()->assertJsonPath('data.qty', 3);

        $this->actingAs($this->customer, 'sanctum')
            ->patchJson("/api/keranjang/{$item->id}", ['action' => 'decrease'])
            ->assertOk()->assertJsonPath('data.qty', 2);
    }

    public function test_item_customer_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'customer']);
        $item = KeranjangBelanja::create(['user_id' => $lain->id, 'produk_id' => $this->produk->id, 'qty' => 1]);

        $this->actingAs($this->customer, 'sanctum')
            ->patchJson("/api/keranjang/{$item->id}", ['action' => 'increase'])->assertNotFound();
        $this->actingAs($this->customer, 'sanctum')
            ->deleteJson("/api/keranjang/{$item->id}")->assertNotFound();
    }

    public function test_hapus_item(): void
    {
        $item = KeranjangBelanja::create(['user_id' => $this->customer->id, 'produk_id' => $this->produk->id, 'qty' => 1]);

        $this->actingAs($this->customer, 'sanctum')->deleteJson("/api/keranjang/{$item->id}")->assertOk();
        $this->assertDatabaseCount('keranjang_belanja', 0);
    }
}
