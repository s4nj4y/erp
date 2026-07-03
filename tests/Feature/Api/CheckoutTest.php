<?php

namespace Tests\Feature\Api;

use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use App\Models\RekeningBank;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Umkm $umkm;
    private Produk $produk;
    private Bank $bank;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
        $pemilik = User::factory()->create(['role' => 'umkm']);
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        $this->umkm = Umkm::create(['user_id' => $pemilik->id, 'jenis_usaha_id' => $jenis->id, 'nama_umkm' => 'Toko Maju', 'status' => true]);
        $this->produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true]);
        $this->bank = Bank::create(['nama_bank' => 'BRI']);
        RekeningBank::create(['umkm_id' => $this->umkm->id, 'bank_id' => $this->bank->id, 'atas_nama' => 'Pemilik', 'rekening' => '123456']);
        KeranjangBelanja::create(['user_id' => $this->customer->id, 'produk_id' => $this->produk->id, 'qty' => 2]);
    }

    public function test_ringkasan_checkout_berisi_items_total_rekening(): void
    {
        $res = $this->actingAs($this->customer, 'sanctum')->getJson("/api/checkout/{$this->umkm->id}");

        $res->assertOk()
            ->assertJsonPath('data.total', 20000)
            ->assertJsonPath('data.items.0.produk.nama_produk', 'Keripik')
            ->assertJsonPath('data.rekening.0.bank.nama_bank', 'BRI');
    }

    public function test_checkout_umkm_tanpa_item_422(): void
    {
        KeranjangBelanja::query()->delete();

        $this->actingAs($this->customer, 'sanctum')
            ->getJson("/api/checkout/{$this->umkm->id}")->assertUnprocessable();
    }

    public function test_buat_pesanan_mengurangi_stok_dan_mengosongkan_keranjang(): void
    {
        $res = $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/checkout/{$this->umkm->id}", ['bank_id' => $this->bank->id]);

        $res->assertCreated()->assertJsonPath('data.status', 'pending');
        $this->assertSame(3, $this->produk->fresh()->stok);
        $this->assertDatabaseCount('keranjang_belanja', 0);
        $this->assertDatabaseCount('transaksi_detail', 1);
        $this->assertDatabaseHas('stok', ['produk_id' => $this->produk->id, 'status' => 'keluar', 'jumlah_keluar' => 2]);
        $this->assertStringStartsWith('INV-', Transaksi::first()->kode_transaksi);
    }

    public function test_bank_id_bukan_rekening_umkm_422(): void
    {
        $bankLain = Bank::create(['nama_bank' => 'BCA']);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/checkout/{$this->umkm->id}", ['bank_id' => $bankLain->id])
            ->assertUnprocessable();
    }

    public function test_stok_tidak_cukup_422_dan_tanpa_efek_samping(): void
    {
        $this->produk->update(['stok' => 1]);

        $this->actingAs($this->customer, 'sanctum')
            ->postJson("/api/checkout/{$this->umkm->id}", ['bank_id' => $this->bank->id])
            ->assertUnprocessable();

        $this->assertDatabaseCount('transaksi', 0);
        $this->assertSame(1, $this->produk->fresh()->stok);
    }
}
