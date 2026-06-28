<?php

namespace Tests\Feature;

use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Render shell tiap panel — menjaga layout dari error Blade/komponen. */
class LayoutSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_shell_renders(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('IBC Admin');
    }

    public function test_umkm_shell_renders(): void
    {
        $user = User::factory()->create(['role' => 'umkm']);
        Umkm::create(['user_id' => $user->id, 'nama_umkm' => 'Toko T']);
        $this->actingAs($user)->get(route('umkm.dashboard'))->assertOk()->assertSee('Toko Saya');
    }

    public function test_public_shell_renders(): void
    {
        $this->get(route('shop'))->assertOk();
    }

    public function test_produk_detail_renders(): void
    {
        $umkm = Umkm::create(['nama_umkm' => 'Toko T']);
        $produk = Produk::create(['umkm_id' => $umkm->id, 'nama_produk' => 'Kopi', 'harga_modal' => 1, 'harga' => 25000, 'stok' => 5]);
        $this->get(route('produk.show', $produk))->assertOk()->assertSee('Kopi');
    }

    public function test_customer_transaksi_index_renders_badge(): void
    {
        $umkm = Umkm::create(['nama_umkm' => 'Toko T']);
        $customer = User::factory()->create(['role' => 'customer']);
        Transaksi::create([
            'customer_id' => $customer->id, 'umkm_id' => $umkm->id,
            'kode_transaksi' => 'TRX-1', 'tanggal' => now(), 'status' => 'diproses', 'status_bayar' => 'menunggu_verifikasi',
        ]);
        $this->actingAs($customer)->get(route('transaksi.index'))->assertOk()->assertSee('Diproses');
    }
}
