<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

class AuthController extends ApiController
{
    #[OA\Post(path: '/api/register', tags: ['Auth'], summary: 'Registrasi customer baru',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'password_confirmation'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'password_confirmation', type: 'string'),
                new OA\Property(property: 'phone', type: 'string', nullable: true),
            ])),
        responses: [new OA\Response(response: 201, description: 'Terdaftar, kembalikan token'),
            new OA\Response(response: 422, description: 'Validasi gagal')])]
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $user = User::create([...$data, 'role' => 'customer']);

        return $this->respond([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ], 'Registrasi berhasil', 201);
    }

    #[OA\Post(path: '/api/login', tags: ['Auth'], summary: 'Login, kembalikan bearer token',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'password', type: 'string')])),
        responses: [new OA\Response(response: 200, description: 'Token + user'),
            new OA\Response(response: 422, description: 'Kredensial salah / akun nonaktif')])]
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        if (! $user->status) {
            throw ValidationException::withMessages(['email' => 'Akun dinonaktifkan.']);
        }

        return $this->respond([
            'token' => $user->createToken('mobile')->plainTextToken,
            'user' => $user,
        ], 'Login berhasil');
    }

    #[OA\Post(path: '/api/logout', tags: ['Auth'], summary: 'Hapus token aktif', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Token dihapus')])]
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->respond(message: 'Logout berhasil');
    }
}
