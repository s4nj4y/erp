<?php

namespace App\Policies\Concerns;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Logika kepemilikan UMKM untuk policy. Admin selalu lolos lewat before().
 */
trait HandlesUmkmOwnership
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    protected function owns(User $user, ?int $umkmId): bool
    {
        return $umkmId !== null && $umkmId === $user->umkm?->id;
    }

    /** Gagal kepemilikan -> 404, bukan 403, agar eksistensi baris toko lain tak bocor. */
    protected function ownsResponse(User $user, ?int $umkmId): Response
    {
        return $this->owns($user, $umkmId) ? Response::allow() : Response::denyAsNotFound();
    }
}
