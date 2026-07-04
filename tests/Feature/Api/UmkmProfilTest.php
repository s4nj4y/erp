<?php

namespace Tests\Feature\Api;

use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\RekeningBank;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmProfilTest extends TestCase
{
    use RefreshDatabase;

    private User $pemilik;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
    }

    public function test_rute_umkm_butuh_role_umkm(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        // customer role tidak boleh akses
        $this->actingAs($customer, 'sanctum')->getJson('/api/umkm/dashboard')->assertForbidden();

        // unauthenticated user tidak boleh akses
        $this->getJson('/api/umkm/dashboard')->assertForbidden();
    }

    public function test_master_data_tersedia_untuk_user_login(): void
    {
        JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        Bank::create(['nama_bank' => 'BRI']);

        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer, 'sanctum')->getJson('/api/master/jenis-usaha')
            ->assertOk()->assertJsonPath('data.0.nama_usaha', 'Kuliner');
        $this->actingAs($customer, 'sanctum')->getJson('/api/master/bank')
            ->assertOk()->assertJsonPath('data.0.nama_bank', 'BRI');
        $this->actingAs($customer, 'sanctum')->getJson('/api/master/kategori-produk')->assertOk();
    }

    public function test_dashboard_tanpa_profil_umkm_mengembalikan_null(): void
    {
        $res = $this->actingAs($this->pemilik, 'sanctum')->getJson('/api/umkm/dashboard');

        $res->assertOk()
            ->assertJsonPath('data.umkm', null)
            ->assertJsonPath('data.stats.produk', 0);
    }

    public function test_profil_create_lalu_update(): void
    {
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);

        // create (belum punya umkm)
        $this->actingAs($this->pemilik, 'sanctum')->putJson('/api/umkm/profil', [
            'nama_umkm' => 'Toko Baru', 'jenis_usaha_id' => $jenis->id,
        ])->assertOk()->assertJsonPath('data.nama_umkm', 'Toko Baru');

        $this->assertDatabaseHas('umkm', ['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko Baru', 'status' => 1]);

        // update
        $this->actingAs($this->pemilik, 'sanctum')->putJson('/api/umkm/profil', [
            'nama_umkm' => 'Toko Diedit',
        ])->assertOk()->assertJsonPath('data.nama_umkm', 'Toko Diedit');

        $this->assertSame(1, Umkm::count());
    }

    public function test_tambah_dan_hapus_rekening(): void
    {
        $umkm = Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);
        $bank = Bank::create(['nama_bank' => 'BRI']);

        $res = $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/profil/rekening', [
            'bank_id' => $bank->id, 'atas_nama' => 'Pemilik', 'rekening' => '123456',
        ]);
        $res->assertCreated();

        $rek = RekeningBank::first();
        $this->actingAs($this->pemilik, 'sanctum')
            ->deleteJson("/api/umkm/profil/rekening/{$rek->id}")->assertOk();
        $this->assertDatabaseCount('rekening_bank', 0);
    }

    public function test_rekening_umkm_lain_404(): void
    {
        Umkm::create(['user_id' => $this->pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);
        $lain = User::factory()->create(['role' => 'umkm']);
        $umkmLain = Umkm::create(['user_id' => $lain->id, 'nama_umkm' => 'Toko Lain', 'status' => true]);
        $bank = Bank::create(['nama_bank' => 'BRI']);
        $rek = RekeningBank::create(['umkm_id' => $umkmLain->id, 'bank_id' => $bank->id, 'atas_nama' => 'X', 'rekening' => '9']);

        $this->actingAs($this->pemilik, 'sanctum')
            ->deleteJson("/api/umkm/profil/rekening/{$rek->id}")->assertNotFound();
    }

    public function test_rekening_tanpa_profil_409(): void
    {
        $bank = Bank::create(['nama_bank' => 'BRI']);

        $this->actingAs($this->pemilik, 'sanctum')->postJson('/api/umkm/profil/rekening', [
            'bank_id' => $bank->id, 'atas_nama' => 'X', 'rekening' => '1',
        ])->assertStatus(409);
    }
}
