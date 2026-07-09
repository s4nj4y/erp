<?php

namespace Tests\Feature\Api;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmPrediksiTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;
    private Umkm $umkm;
    private Produk $produk;
    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);
        $this->produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik',
            'harga_modal' => 5000, 'harga' => 10000, 'stok' => 10, 'show' => true]);
        $this->customer = User::factory()->create(['role' => 'customer']);
    }

    /** Penjualan terverifikasi qty tertentu pada N hari lalu. */
    private function jual(int $hariLalu, int $qty, string $kode): void
    {
        $trx = Transaksi::create(['customer_id' => $this->customer->id, 'umkm_id' => $this->umkm->id,
            'kode_transaksi' => $kode, 'tanggal' => now()->subDays($hariLalu)->setTime(10, 0),
            'status' => 'diproses', 'status_bayar' => 'terverifikasi']);
        $trx->detail()->create(['produk_id' => $this->produk->id, 'qty' => $qty, 'harga' => 10000]);
    }

    public function test_forecast_omzet_dari_penjualan_konstan(): void
    {
        // 7 hari penuh @ 1 x 10000 (periode 7d) → regresi datar, proyeksi ≈ 10000/hari
        foreach (range(0, 6) as $i) {
            $this->jual($i, 1, "INV-F$i");
        }

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/prediksi/omzet?periode=7d');

        $res->assertOk()->assertJsonPath('data.periode', '7d');
        $forecast = $res->json('data.forecast');
        $this->assertNotNull($forecast);
        $this->assertCount(7, $forecast['nilai']);
        $this->assertSame('7 hari', $forecast['horizon']);
        // penjualan konstan: total proyeksi 7 hari mendekati 7 x 10000
        $this->assertEqualsWithDelta(70000, $forecast['total'], 7000);
    }

    public function test_forecast_null_bila_data_belum_cukup(): void
    {
        $this->jual(0, 1, 'INV-X1'); // penjualan toko lain tidak boleh bocor ke toko kosong

        // UMKM baru tanpa transaksi sama sekali → semua titik nol → null
        $baru = User::factory()->create(['role' => 'umkm']);
        Umkm::create(['user_id' => $baru->id, 'nama_umkm' => 'Kosong', 'status' => true]);

        $this->actingAs($baru, 'sanctum')->getJson('/api/umkm/prediksi/omzet')
            ->assertOk()->assertJsonPath('data.forecast', null);
    }

    public function test_stok_habis(): void
    {
        // 15 qty terjual dalam 30 hari, stok 10 → laju 0.5/hari → habis ±20 hari
        $this->jual(5, 15, 'INV-S1');

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/prediksi/stok-habis');

        $res->assertOk()
            ->assertJsonPath('data.rows.0.nama', 'Keripik')
            ->assertJsonPath('data.rows.0.hari_tersisa', 20);
    }

    public function test_produk_trending_mendeteksi_kenaikan(): void
    {
        // qty menaik mendekati hari ini → slope positif
        $this->jual(6, 1, 'INV-T1');
        $this->jual(4, 2, 'INV-T2');
        $this->jual(2, 4, 'INV-T3');
        $this->jual(0, 6, 'INV-T4');

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/prediksi/produk-trending');

        $res->assertOk()->assertJsonPath('data.rows.0.nama', 'Keripik');
        $this->assertGreaterThan(0, $res->json('data.rows.0.slope'));
    }

    public function test_periode_tidak_valid_422(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')
            ->getJson('/api/umkm/prediksi/omzet?periode=abc')->assertStatus(422);
    }

    public function test_tanpa_profil_409(): void
    {
        $baru = User::factory()->create(['role' => 'umkm']);

        $this->actingAs($baru, 'sanctum')->getJson('/api/umkm/prediksi/omzet')->assertStatus(409);
    }
}
