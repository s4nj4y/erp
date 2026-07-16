<?php

namespace Tests\Feature\Api;

use App\Models\Saldo;
use App\Models\TransaksiPengeluaran;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmKeuanganTest extends TestCase
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

    public function test_saldo_tambah_dan_modal_berjalan(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/saldo', [
            'tanggal_transaksi' => '2026-07-01', 'jenis_transaksi' => 'investasi_awal', 'jumlah' => 100000,
        ])->assertCreated();
        $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/saldo', [
            'tanggal_transaksi' => '2026-07-02', 'jenis_transaksi' => 'pengambilan_modal', 'jumlah' => 30000,
        ])->assertCreated();

        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/saldo');
        $res->assertOk()->assertJsonPath('data.modal_saat_ini', 70000);
    }

    public function test_hapus_saldo_recalculate(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/saldo', [
            'tanggal_transaksi' => '2026-07-01', 'jenis_transaksi' => 'investasi_awal', 'jumlah' => 100000,
        ]);
        $s = Saldo::first();

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/saldo/{$s->id}")->assertOk();
        $this->assertSame(0, $this->actingAs($this->pemilik, 'sanctum')
            ->getJson('/api/umkm/saldo')->json('data.modal_saat_ini'));
    }

    public function test_saldo_umkm_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);
        $s = Saldo::create(['umkm_id' => $umkmLain->id, 'tanggal_transaksi' => '2026-07-01',
            'jenis_transaksi' => 'investasi_awal', 'jumlah' => 1, 'saldo' => 1]);

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/saldo/{$s->id}")->assertNotFound();
    }

    public function test_pengeluaran_buat_dengan_items_dan_total(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/pengeluaran', [
            'tanggal_pengeluaran' => '2026-07-04',
            'items' => [
                ['keterangan' => 'Plastik', 'qty' => 10, 'harga' => 1000],
                ['keterangan' => 'Minyak', 'qty' => 2, 'harga' => 15000],
            ],
        ]);

        $res->assertCreated()->assertJsonPath('data.total_harga', 40000);
        $this->assertDatabaseCount('transaksi_pengeluaran_detail', 2);

        $list = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/pengeluaran');
        $list->assertOk()->assertJsonPath('data.total', 40000);
    }

    public function test_pengeluaran_items_kosong_422(): void
    {
        $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/pengeluaran', [
            'tanggal_pengeluaran' => '2026-07-04', 'items' => [],
        ])->assertUnprocessable();
    }

    public function test_pengeluaran_umkm_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);
        $p = TransaksiPengeluaran::create(['umkm_id' => $umkmLain->id,
            'tanggal_pengeluaran' => '2026-07-04', 'total_harga' => 1]);

        $this->actingAs($this->pemilik, 'sanctum')->getJson("/api/umkm/pengeluaran/{$p->id}")->assertNotFound();
    }

    public function test_hapus_pengeluaran(): void
    {
        $p = TransaksiPengeluaran::create(['umkm_id' => $this->umkm->id,
            'tanggal_pengeluaran' => '2026-07-04', 'total_harga' => 5000]);

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/pengeluaran/{$p->id}")->assertOk();
        $this->assertDatabaseCount('transaksi_pengeluaran', 0);
    }

    public function test_hapus_pengeluaran_umkm_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Lain', 'status' => true]);
        $p = TransaksiPengeluaran::create(['umkm_id' => $umkmLain->id,
            'tanggal_pengeluaran' => '2026-07-04', 'total_harga' => 1]);

        $this->actingAs($this->pemilik, 'sanctum')->deleteJson("/api/umkm/pengeluaran/{$p->id}")->assertNotFound();
        $this->assertDatabaseCount('transaksi_pengeluaran', 1);
    }
}
