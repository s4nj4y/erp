<?php

namespace App\Policies;

use App\Models\KeranjangBelanja;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Keranjang bersifat privat per-customer. Tanpa bypass admin/umkm.
 * Gagal -> 404 agar item customer lain tak terdeteksi.
 */
class KeranjangBelanjaPolicy
{
    public function update(User $user, KeranjangBelanja $keranjang): Response
    {
        return $this->ownsCart($user, $keranjang);
    }

    public function delete(User $user, KeranjangBelanja $keranjang): Response
    {
        return $this->ownsCart($user, $keranjang);
    }

    private function ownsCart(User $user, KeranjangBelanja $keranjang): Response
    {
        return $keranjang->user_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
