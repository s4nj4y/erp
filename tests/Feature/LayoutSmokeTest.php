<?php

namespace Tests\Feature;

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
}
