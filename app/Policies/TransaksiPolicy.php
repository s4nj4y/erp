<?php

namespace App\Policies;

use App\Models\Transaksi;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class TransaksiPolicy
{
    use HandlesUmkmOwnership;

    /** Sisi UMKM: lihat pesanan masuk. */
    public function viewAsUmkm(User $user, Transaksi $transaksi): Response
    {
        return $this->ownsResponse($user, $transaksi->umkm_id);
    }

    /** Sisi UMKM: verifikasi/tolak/kirim. */
    public function manage(User $user, Transaksi $transaksi): Response
    {
        return $this->ownsResponse($user, $transaksi->umkm_id);
    }

    /** Sisi customer: lihat/bayar/terima pesanannya sendiri. */
    public function viewAsCustomer(User $user, Transaksi $transaksi): Response
    {
        return $transaksi->customer_id === $user->id
            ? Response::allow()
            : Response::denyAsNotFound();
    }
}
