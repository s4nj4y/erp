<?php

namespace App\Http\Controllers\Api\Umkm;

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Umkm\Concerns\ResolvesUmkm;
use App\Models\RekeningBank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class ProfilController extends ApiController
{
    use ResolvesUmkm;

    #[OA\Get(path: '/api/umkm/profil', tags: ['UMKM'], summary: 'Profil toko + rekening', security: [['bearerAuth' => []]],
        responses: [new OA\Response(response: 200, description: 'Umkm atau null bila belum dibuat')])]
    public function show(Request $request)
    {
        $umkm = \App\Models\Umkm::where('user_id', $request->user()->id)->first();
        $umkm?->load('jenisUsaha', 'rekening.bank');

        return $this->respond($umkm);
    }

    #[OA\Put(path: '/api/umkm/profil', tags: ['UMKM'], summary: 'Buat/ubah profil toko (multipart pakai POST + _method=PUT utk foto)', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(required: ['nama_umkm'], properties: [
            new OA\Property(property: 'nama_umkm', type: 'string'),
            new OA\Property(property: 'email', type: 'string', nullable: true),
            new OA\Property(property: 'no_wa', type: 'string', nullable: true),
            new OA\Property(property: 'alamat', type: 'string', nullable: true),
            new OA\Property(property: 'deskripsi', type: 'string', nullable: true),
            new OA\Property(property: 'tgl_pendirian', type: 'string', format: 'date', nullable: true),
            new OA\Property(property: 'nama_pendiri', type: 'string', nullable: true),
            new OA\Property(property: 'jenis_usaha_id', type: 'integer', nullable: true)])),
        responses: [new OA\Response(response: 200, description: 'Umkm tersimpan')])]
    public function update(Request $request)
    {
        $data = $request->validate([
            'nama_umkm' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'no_wa' => 'nullable|string|max:20',
            'alamat' => 'nullable|string|max:150',
            'deskripsi' => 'nullable|string|max:255',
            'tgl_pendirian' => 'nullable|date',
            'nama_pendiri' => 'nullable|string|max:100',
            'jenis_usaha_id' => ['nullable', Rule::exists('jenis_usaha', 'id')],
            'foto' => 'nullable|image|max:2048',
        ]);

        $umkm = \App\Models\Umkm::where('user_id', $request->user()->id)->first();

        if ($request->hasFile('foto')) {
            if ($umkm?->foto && ! str_starts_with($umkm->foto, 'http')) {
                Storage::disk('public')->delete($umkm->foto);
            }
            $data['foto'] = $request->file('foto')->store('umkm', 'public');
        }

        if ($umkm) {
            $umkm->update($data);
        } else {
            $data['user_id'] = $request->user()->id;
            $data['status'] = true;
            $umkm = \App\Models\Umkm::create($data);
        }

        return $this->respond($umkm->fresh()->load('jenisUsaha', 'rekening.bank'), 'Profil UMKM disimpan.');
    }

    #[OA\Post(path: '/api/umkm/profil/rekening', tags: ['UMKM'], summary: 'Tambah rekening bank toko', security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            required: ['bank_id', 'atas_nama', 'rekening'], properties: [
                new OA\Property(property: 'bank_id', type: 'integer'),
                new OA\Property(property: 'atas_nama', type: 'string'),
                new OA\Property(property: 'rekening', type: 'string')])),
        responses: [new OA\Response(response: 201, description: 'Rekening dibuat'),
            new OA\Response(response: 409, description: 'Belum ada profil UMKM')])]
    public function storeRekening(Request $request)
    {
        $umkm = \App\Models\Umkm::where('user_id', $request->user()->id)->first();
        abort_if(! $umkm, 409, 'Lengkapi profil UMKM dulu.');

        $data = $request->validate([
            'bank_id' => ['required', Rule::exists('bank', 'id')],
            'atas_nama' => 'required|string|max:100',
            'rekening' => 'required|string|max:60',
        ]);
        $data['status'] = true;
        $rekening = $umkm->rekening()->create($data);

        return $this->respond($rekening->load('bank'), 'Rekening ditambahkan.', 201);
    }

    #[OA\Delete(path: '/api/umkm/profil/rekening/{rekening}', tags: ['UMKM'], summary: 'Hapus rekening', security: [['bearerAuth' => []]],
        parameters: [new OA\Parameter(name: 'rekening', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Terhapus'),
            new OA\Response(response: 404, description: 'Bukan milik toko user')])]
    public function destroyRekening(Request $request, RekeningBank $rekening)
    {
        $this->authorize('delete', $rekening);
        $rekening->delete();

        return $this->respond(message: 'Rekening dihapus.');
    }
}
