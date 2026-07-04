<?php

namespace Tests\Feature\Api;

use App\Models\Produk;
use App\Models\Saldo;
use App\Models\Transaksi;
use App\Models\TransaksiPengeluaran;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmLaporanTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;
    private Umkm $umkm;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);

        // Penjualan terverifikasi: 2 x 10000, modal 5000 → pendapatan 20000, HPP 10000
        $produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik',
            'harga_modal' => 5000, 'harga' => 10000, 'stok' => 5, 'show' => true]);
        $customer = User::factory()->create(['role' => 'customer']);
        $trx = Transaksi::create(['customer_id' => $customer->id, 'umkm_id' => $this->umkm->id,
            'kode_transaksi' => 'INV-L1', 'tanggal' => '2026-07-02 10:00:00',
            'status' => 'diproses', 'status_bayar' => 'terverifikasi']);
        $trx->detail()->create(['produk_id' => $produk->id, 'qty' => 2, 'harga' => 10000]);

        // Pengeluaran 4000 dalam periode
        TransaksiPengeluaran::create(['umkm_id' => $this->umkm->id,
            'tanggal_pengeluaran' => '2026-07-03', 'total_harga' => 4000]);

        // Modal: investasi 100000 sebelum periode, penambahan 50000 dalam periode
        Saldo::create(['umkm_id' => $this->umkm->id, 'tanggal_transaksi' => '2026-06-01',
            'jenis_transaksi' => 'investasi_awal', 'jumlah' => 100000, 'saldo' => 100000]);
        Saldo::create(['umkm_id' => $this->umkm->id, 'tanggal_transaksi' => '2026-07-02',
            'jenis_transaksi' => 'penambahan_modal', 'jumlah' => 50000, 'saldo' => 150000]);
    }

    private function q(): string
    {
        return '?from=2026-07-01&to=2026-07-31';
    }

    public function test_laba_rugi(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/laporan/laba-rugi'.$this->q());

        $res->assertOk()
            ->assertJsonPath('data.pendapatan', 20000)
            ->assertJsonPath('data.hpp', 10000)
            ->assertJsonPath('data.laba_kotor', 10000)
            ->assertJsonPath('data.pengeluaran', 4000)
            ->assertJsonPath('data.laba_bersih', 6000);
    }

    public function test_pendapatan_rows(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/laporan/pendapatan'.$this->q());

        $res->assertOk()
            ->assertJsonPath('data.total_pendapatan', 20000)
            ->assertJsonPath('data.rows.0.kode', 'INV-L1')
            ->assertJsonPath('data.rows.0.total', 20000);
    }

    public function test_perubahan_modal(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/laporan/perubahan-modal'.$this->q());

        $res->assertOk()
            ->assertJsonPath('data.modal_awal', 100000)
            ->assertJsonPath('data.penambahan', 50000)
            ->assertJsonPath('data.laba_bersih', 6000)
            ->assertJsonPath('data.pengambilan', 0)
            ->assertJsonPath('data.modal_akhir', 156000);
    }

    public function test_tanpa_profil_409(): void
    {
        $baru = User::factory()->create(['role' => 'umkm']);

        $this->actingAs($baru, 'sanctum')->getJson('/api/umkm/laporan/laba-rugi')->assertStatus(409);
    }
}
