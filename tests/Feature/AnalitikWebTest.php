<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalitikWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_analitik_umkm(): void
    {
        $pemilik = User::factory()->create(['role' => 'umkm']);
        Umkm::create(['user_id' => $pemilik->id, 'nama_umkm' => 'Toko', 'status' => true]);

        $this->actingAs($pemilik)->get('/umkm/analitik?periode=7d')
            ->assertOk()->assertSee('Tren Omzet')->assertSee('Produk Terlaris')
            ->assertSee('Prediksi Omzet')->assertSee('Data penjualan belum cukup');
    }

    public function test_umkm_tanpa_profil_diarahkan_ke_profil(): void
    {
        $pemilik = User::factory()->create(['role' => 'umkm']);

        $this->actingAs($pemilik)->get('/umkm/analitik')->assertRedirect(route('umkm.profil.edit'));
    }

    public function test_halaman_analitik_admin(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/analitik')
            ->assertOk()->assertSee('Tren GMV')->assertSee('UMKM Teratas')->assertSee('Prediksi Platform');
    }

    public function test_umkm_tidak_boleh_akses_analitik_admin(): void
    {
        $pemilik = User::factory()->create(['role' => 'umkm']);

        $this->actingAs($pemilik)->get('/admin/analitik')->assertForbidden();
    }
}
