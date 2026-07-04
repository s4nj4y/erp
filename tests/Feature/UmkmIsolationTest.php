<?php

namespace Tests\Feature;

use App\Models\KeranjangBelanja;
use App\Models\Produk;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UmkmIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function umkmUser(): array
    {
        $user = User::factory()->create(['role' => 'umkm']);
        $umkm = Umkm::create(['user_id' => $user->id, 'nama_umkm' => 'Toko '.$user->id]);

        return [$user, $umkm];
    }

    public function test_umkm_cannot_update_other_umkm_produk(): void
    {
        [, $umkmA] = $this->umkmUser();
        [$userB] = $this->umkmUser();
        $produkA = Produk::create(['umkm_id' => $umkmA->id, 'nama_produk' => 'A', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1]);

        $this->actingAs($userB)
            ->put(route('umkm.produk.update', $produkA), [
                'nama_produk' => 'Hijacked', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1,
            ])
            ->assertNotFound();

        $this->assertSame('A', $produkA->fresh()->nama_produk);
    }

    public function test_umkm_can_update_own_produk(): void
    {
        [$userA, $umkmA] = $this->umkmUser();
        $produkA = Produk::create(['umkm_id' => $umkmA->id, 'nama_produk' => 'A', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1]);

        $this->actingAs($userA)
            ->put(route('umkm.produk.update', $produkA), [
                'nama_produk' => 'Updated', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1,
            ])
            ->assertRedirect(route('umkm.produk.index'));

        $this->assertSame('Updated', $produkA->fresh()->nama_produk);
    }

    public function test_umkm_cannot_manage_other_umkm_transaksi(): void
    {
        [, $umkmA] = $this->umkmUser();
        [$userB] = $this->umkmUser();
        $customer = User::factory()->create(['role' => 'customer']);
        $trx = Transaksi::create([
            'customer_id' => $customer->id, 'umkm_id' => $umkmA->id,
            'kode_transaksi' => 'TRX-A', 'tanggal' => now(),
        ]);

        $this->actingAs($userB)
            ->post(route('umkm.transaksi.verifikasi', $trx))
            ->assertNotFound();
    }

    public function test_customer_cannot_view_other_customer_transaksi(): void
    {
        [, $umkmA] = $this->umkmUser();
        $owner = User::factory()->create(['role' => 'customer']);
        $intruder = User::factory()->create(['role' => 'customer']);
        $trx = Transaksi::create([
            'customer_id' => $owner->id, 'umkm_id' => $umkmA->id,
            'kode_transaksi' => 'TRX-B', 'tanggal' => now(),
        ]);

        $this->actingAs($intruder)
            ->get(route('transaksi.show', $trx))
            ->assertNotFound();
    }

    public function test_customer_cannot_delete_other_customer_cart_item(): void
    {
        [, $umkmA] = $this->umkmUser();
        $produk = Produk::create(['umkm_id' => $umkmA->id, 'nama_produk' => 'A', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1]);
        $owner = User::factory()->create(['role' => 'customer']);
        $intruder = User::factory()->create(['role' => 'customer']);
        $item = KeranjangBelanja::create(['user_id' => $owner->id, 'produk_id' => $produk->id, 'qty' => 1]);

        $this->actingAs($intruder)
            ->delete(route('cart.destroy', $item))
            ->assertNotFound();

        $this->assertDatabaseHas('keranjang_belanja', ['id' => $item->id]);
    }

    public function test_tolak_dua_kali_via_web_stok_hanya_kembali_sekali(): void
    {
        [$userA, $umkmA] = $this->umkmUser();
        $produk = Produk::create(['umkm_id' => $umkmA->id, 'nama_produk' => 'A', 'harga' => 10000, 'stok' => 3]);
        $customer = User::factory()->create(['role' => 'customer']);
        $trx = Transaksi::create([
            'customer_id' => $customer->id, 'umkm_id' => $umkmA->id,
            'kode_transaksi' => 'TRX-C', 'tanggal' => now(),
            'status' => 'pending', 'status_bayar' => 'menunggu_verifikasi',
        ]);
        $trx->detail()->create(['produk_id' => $produk->id, 'qty' => 2, 'harga' => 10000]);

        $this->actingAs($userA)
            ->post(route('umkm.transaksi.tolak', $trx))
            ->assertRedirect();

        $this->actingAs($userA)
            ->post(route('umkm.transaksi.tolak', $trx))
            ->assertRedirect();

        $this->assertSame(5, $produk->fresh()->stok); // 3 + 2, bukan dua kali
        $this->assertDatabaseCount('stok', 1);
    }

    public function test_admin_bypasses_ownership_via_policy(): void
    {
        [, $umkmA] = $this->umkmUser();
        $produkA = Produk::create(['umkm_id' => $umkmA->id, 'nama_produk' => 'A', 'harga_modal' => 1, 'harga' => 2, 'stok' => 1]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($admin->can('update', $produkA));
    }
}
