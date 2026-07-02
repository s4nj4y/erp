<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProfileController extends ApiController
{
    #[OA\Get(path: '/api/profile', tags: ['Profil'], summary: 'Profil user login', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Data user'),
            new OA\Response(response: 401, description: 'Belum login')])]
    public function show(Request $request)
    {
        return $this->respond($request->user());
    }

    #[OA\Patch(path: '/api/profile', tags: ['Profil'], summary: 'Ubah profil', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name', type: 'string'),
            new OA\Property(property: 'email', type: 'string'),
            new OA\Property(property: 'phone', type: 'string')])),
        responses: [new OA\Response(response: 200, description: 'Profil diperbarui')])]
    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($request->user()->id)],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        $request->user()->update($data);

        return $this->respond($request->user()->fresh(), 'Profil diperbarui');
    }
}
