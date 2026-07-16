<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_membuat_customer_dan_mengembalikan_token(): void
    {
        $res = $this->postJson('/api/register', [
            'name' => 'Budi', 'email' => 'budi@example.com',
            'password' => 'password123', 'password_confirmation' => 'password123',
        ]);

        $res->assertCreated()->assertJsonStructure(['data' => ['token', 'user' => ['id', 'name', 'role']]]);
        $this->assertSame('customer', $res->json('data.user.role'));
    }

    public function test_login_email_password_benar_mengembalikan_token(): void
    {
        $user = User::factory()->create(['password' => 'rahasia123']);

        $res = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'rahasia123']);

        $res->assertOk()->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_password_salah_gagal_422(): void
    {
        $user = User::factory()->create();

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'salah'])
            ->assertUnprocessable();
    }

    public function test_login_akun_nonaktif_ditolak(): void
    {
        $user = User::factory()->create(['status' => false, 'password' => 'rahasia123']);

        $this->postJson('/api/login', ['email' => $user->email, 'password' => 'rahasia123'])
            ->assertUnprocessable();
    }

    public function test_profile_butuh_token(): void
    {
        $this->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_profile_dan_update(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')->getJson('/api/profile')
            ->assertOk()->assertJsonPath('data.email', $user->email);

        $this->actingAs($user, 'sanctum')->patchJson('/api/profile', ['name' => 'Nama Baru', 'phone' => '0812345'])
            ->assertOk()->assertJsonPath('data.name', 'Nama Baru');
    }

    public function test_logout_menghapus_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertSame(0, $user->tokens()->count());
    }

    public function test_token_ditolak_setelah_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $this->withToken($token)->postJson('/api/logout')->assertOk();

        // reset guard yang meng-cache user pada request pertama dalam proses tes yang sama
        $this->app['auth']->forgetGuards();
        $this->flushHeaders();
        $this->withToken($token)->getJson('/api/profile')->assertUnauthorized();
    }

    public function test_update_profile_mengabaikan_field_role(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user, 'sanctum')->patchJson('/api/profile', [
            'name' => 'Nama Baru', 'role' => 'umkm',
        ])->assertOk();

        $this->assertSame('customer', $user->fresh()->role);
    }
}
