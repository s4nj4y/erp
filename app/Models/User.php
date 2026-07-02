<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = ['name', 'username', 'email', 'password', 'role', 'status', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'boolean',
        ];
    }

    public function isAdmin(): bool { return $this->role === 'admin'; }
    public function isUmkm(): bool { return $this->role === 'umkm'; }
    public function isCustomer(): bool { return $this->role === 'customer'; }

    public function umkm(): HasOne { return $this->hasOne(Umkm::class); }
    public function keranjang(): HasMany { return $this->hasMany(KeranjangBelanja::class); }
    public function transaksi(): HasMany { return $this->hasMany(Transaksi::class, 'customer_id'); }
}
