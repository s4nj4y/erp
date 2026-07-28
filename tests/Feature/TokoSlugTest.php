<?php

namespace Tests\Feature;

use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TokoSlugTest extends TestCase
{
    use RefreshDatabase;

    private function buatUmkm(array $attrs = []): Umkm
    {
        $user = User::factory()->create(['role' => 'umkm']);

        return Umkm::create(array_merge(['user_id' => $user->id, 'nama_umkm' => 'Dapur Lampung'], $attrs));
    }

    public function test_slug_tergenerate_otomatis_saat_create(): void
    {
        $this->assertSame('dapur-lampung', $this->buatUmkm()->slug);
    }

    public function test_nama_duplikat_dapat_akhiran_unik(): void
    {
        $this->buatUmkm();
        $this->assertSame('dapur-lampung-2', $this->buatUmkm()->slug);
    }

    public function test_slug_stabil_saat_nama_diubah(): void
    {
        $umkm = $this->buatUmkm();
        $umkm->update(['nama_umkm' => 'Dapur Baru']);
        $this->assertSame('dapur-lampung', $umkm->fresh()->slug);
    }
}
