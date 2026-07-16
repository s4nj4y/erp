<?php

namespace Tests\Feature\Api;

use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransaksiCustomerTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Transaksi $trx;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
        $pemilik = User::factory()->create(['role' => 'umkm']);
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        $umkm = Umkm::create(['user_id' => $pemilik->id, 'jenis_usaha_id' => $jenis->id, 'nama_umkm' => 'Toko Maju', 'status' => true]);
        $produk = Produk::create(['umkm_id' => $umkm->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true]);
        $bank = Bank::create(['nama_bank' => 'BRI']);
        $this->trx = Transaksi::create([
            'customer_id' => $this->customer->id, 'umkm_id' => $umkm->id, 'bank_id' => $bank->id,
            'kode_transaksi' => 'INV-TEST-1', 'tanggal' => now(), 'status' => 'pending', 'status_bayar' => 'belum',
        ]);
        $this->trx->detail()->create(['produk_id' => $produk->id, 'qty' => 2, 'harga' => 10000]);
    }

    public function test_daftar_transaksi_milik_sendiri_saja(): void
    {
        $lain = User::factory()->create(['role' => 'customer']);
        Transaksi::create([
            'customer_id' => $lain->id, 'umkm_id' => $this->trx->umkm_id,
            'kode_transaksi' => 'INV-TEST-2', 'tanggal' => now(),
        ]);

        $res = $this->actingAs($this->customer, 'sanctum')->getJson('/api/transaksi');

        $res->assertOk();
        $this->assertCount(1, $res->json('data.data'));
        $this->assertSame('INV-TEST-1', $res->json('data.data.0.kode_transaksi'));
    }

    public function test_detail_transaksi_dengan_total(): void
    {
        $res = $this->actingAs($this->customer, 'sanctum')->getJson("/api/transaksi/{$this->trx->id}");

        $res->assertOk()
            ->assertJsonPath('data.total', 20000)
            ->assertJsonPath('data.transaksi.detail.0.produk.nama_produk', 'Keripik');
    }

    public function test_transaksi_customer_lain_404(): void
    {
        $lain = User::factory()->create(['role' => 'customer']);

        $this->actingAs($lain, 'sanctum')->getJson("/api/transaksi/{$this->trx->id}")->assertNotFound();
    }

    public function test_upload_bukti_mengubah_status_bayar(): void
    {
        Storage::fake('public');

        $res = $this->actingAs($this->customer, 'sanctum')->post("/api/transaksi/{$this->trx->id}/bukti", [
            'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
        ], ['Accept' => 'application/json']);

        $res->assertOk();
        $this->trx->refresh();
        $this->assertSame('menunggu_verifikasi', $this->trx->status_bayar);
        Storage::disk('public')->assertExists($this->trx->bukti_pembayaran);
    }

    public function test_upload_bukan_gambar_422(): void
    {
        Storage::fake('public');

        $this->actingAs($this->customer, 'sanctum')->post("/api/transaksi/{$this->trx->id}/bukti", [
            'bukti_pembayaran' => UploadedFile::fake()->create('doc.pdf', 100),
        ], ['Accept' => 'application/json'])->assertUnprocessable();
    }

    public function test_upload_bukti_transaksi_customer_lain_404(): void
    {
        Storage::fake('public');
        $lain = User::factory()->create(['role' => 'customer']);

        $this->actingAs($lain, 'sanctum')->post("/api/transaksi/{$this->trx->id}/bukti", [
            'bukti_pembayaran' => UploadedFile::fake()->image('bukti.jpg'),
        ], ['Accept' => 'application/json'])->assertNotFound();

        $this->assertNull($this->trx->fresh()->bukti_pembayaran);
    }

    public function test_terima_pesanan_mengubah_status(): void
    {
        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/transaksi/{$this->trx->id}/terima")
            ->assertOk();

        $this->assertSame('selesai', $this->trx->fresh()->status);
    }
}
