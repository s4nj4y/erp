<?php

namespace Tests\Feature\Api;

use App\Models\JenisUsaha;
use App\Models\Produk;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublikTest extends TestCase
{
    use RefreshDatabase;

    private function buatToko(array $umkmAttrs = []): Umkm
    {
        $user = User::factory()->create(['role' => 'umkm']);
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);

        return Umkm::create(array_merge([
            'user_id' => $user->id, 'jenis_usaha_id' => $jenis->id,
            'nama_umkm' => 'Toko Maju', 'status' => true,
        ], $umkmAttrs));
    }

    public function test_daftar_toko_hanya_yang_aktif(): void
    {
        $this->buatToko();
        $this->buatToko(['nama_umkm' => 'Toko Tutup', 'status' => false]);

        $res = $this->getJson('/api/toko');

        $res->assertOk();
        $this->assertCount(1, $res->json('data.data'));
        $this->assertSame('Toko Maju', $res->json('data.data.0.nama_umkm'));
    }

    public function test_pencarian_toko(): void
    {
        $this->buatToko();
        $this->buatToko(['nama_umkm' => 'Warung Berkah']);

        $res = $this->getJson('/api/toko?q=Berkah');

        $this->assertCount(1, $res->json('data.data'));
    }

    public function test_detail_toko_nonaktif_404(): void
    {
        $toko = $this->buatToko(['status' => false]);

        $this->getJson("/api/toko/{$toko->id}")->assertNotFound();
    }

    public function test_daftar_produk_hanya_show_dengan_filter_toko(): void
    {
        $toko = $this->buatToko();
        Produk::create(['umkm_id' => $toko->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true]);
        Produk::create(['umkm_id' => $toko->id, 'nama_produk' => 'Rahasia', 'harga' => 10000, 'stok' => 5, 'show' => false]);

        $res = $this->getJson("/api/produk?umkm={$toko->id}");

        $res->assertOk();
        $this->assertCount(1, $res->json('data.data'));
    }

    public function test_detail_produk_menyertakan_toko_dan_gambar_url(): void
    {
        $toko = $this->buatToko();
        $p = Produk::create(['umkm_id' => $toko->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true, 'gambar' => 'https://contoh.test/k.jpg']);

        $res = $this->getJson("/api/produk/{$p->id}");

        $res->assertOk()
            ->assertJsonPath('data.umkm.nama_umkm', 'Toko Maju')
            ->assertJsonPath('data.gambar_url', 'https://contoh.test/k.jpg');
    }
}
