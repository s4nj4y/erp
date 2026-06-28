<?php

namespace App\Policies;

use App\Models\Produk;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class ProdukPolicy
{
    use HandlesUmkmOwnership;

    public function update(User $user, Produk $produk): Response
    {
        return $this->ownsResponse($user, $produk->umkm_id);
    }

    public function delete(User $user, Produk $produk): Response
    {
        return $this->ownsResponse($user, $produk->umkm_id);
    }

    public function manageStock(User $user, Produk $produk): Response
    {
        return $this->ownsResponse($user, $produk->umkm_id);
    }
}
