<?php

namespace Tests\Feature\Blackbox;

use App\Models\Bank;
use App\Models\JenisUsaha;
use App\Models\KeranjangBelanja;
use App\Models\Produk;
use App\Models\RekeningBank;
use App\Models\Transaksi;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Basis bersama eksperimen perbandingan teknik black-box (paper JSI):
 * satu UMKM dengan satu produk (stok 5, harga 10000) dan satu rekening bank.
 */
abstract class BlackboxTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $pemilik;
    protected Umkm $umkm;
    protected Produk $produk;
    protected Bank $bank;

    protected function setUp(): void
    {
        parent::setUp();
        $this->customer = User::factory()->create(['role' => 'customer']);
        $this->pemilik = User::factory()->create(['role' => 'umkm']);
        $jenis = JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        $this->umkm = Umkm::create(['user_id' => $this->pemilik->id, 'jenis_usaha_id' => $jenis->id, 'nama_umkm' => 'Toko Maju', 'status' => true]);
        $this->produk = Produk::create(['umkm_id' => $this->umkm->id, 'nama_produk' => 'Keripik', 'harga' => 10000, 'stok' => 5, 'show' => true]);
        $this->bank = Bank::create(['nama_bank' => 'BRI']);
        RekeningBank::create(['umkm_id' => $this->umkm->id, 'bank_id' => $this->bank->id, 'atas_nama' => 'Pemilik', 'rekening' => '123456']);
    }

    /** Isi keranjang customer dengan produk uji. */
    protected function isiKeranjang(int $qty = 2): KeranjangBelanja
    {
        return KeranjangBelanja::create(['user_id' => $this->customer->id, 'produk_id' => $this->produk->id, 'qty' => $qty]);
    }

    /** Buat transaksi langsung pada state tertentu (stok ikut dikurangi agar konsisten checkout). */
    protected function transaksiPadaState(string $status = 'pending', string $statusBayar = 'belum', int $qty = 2): Transaksi
    {
        $trx = Transaksi::create([
            'customer_id' => $this->customer->id,
            'umkm_id' => $this->umkm->id,
            'bank_id' => $this->bank->id,
            'kode_transaksi' => 'INV-TEST-'.Str::upper(Str::random(6)),
            'tanggal' => now(),
            'status' => $status,
            'status_bayar' => $statusBayar,
        ]);
        $trx->detail()->create(['produk_id' => $this->produk->id, 'qty' => $qty, 'harga' => $this->produk->harga]);
        $this->produk->decrement('stok', $qty);

        return $trx;
    }
}
