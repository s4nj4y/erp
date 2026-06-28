<?php

namespace App\Models;

use App\Models\Concerns\BelongsToUmkm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekeningBank extends Model
{
    use BelongsToUmkm;

    protected $table = 'rekening_bank';
    protected $guarded = [];
    protected $casts = ['status' => 'boolean'];

    public function umkm(): BelongsTo { return $this->belongsTo(Umkm::class, 'umkm_id'); }
    public function bank(): BelongsTo { return $this->belongsTo(Bank::class, 'bank_id'); }
}
