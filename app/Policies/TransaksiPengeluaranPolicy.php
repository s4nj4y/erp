<?php

namespace App\Policies;

use App\Models\TransaksiPengeluaran;
use App\Models\User;
use App\Policies\Concerns\HandlesUmkmOwnership;
use Illuminate\Auth\Access\Response;

class TransaksiPengeluaranPolicy
{
    use HandlesUmkmOwnership;

    public function view(User $user, TransaksiPengeluaran $pengeluaran): Response
    {
        return $this->ownsResponse($user, $pengeluaran->umkm_id);
    }

    public function delete(User $user, TransaksiPengeluaran $pengeluaran): Response
    {
        return $this->ownsResponse($user, $pengeluaran->umkm_id);
    }
}
