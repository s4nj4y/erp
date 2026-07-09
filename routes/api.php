<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\KeranjangController;
use App\Http\Controllers\Api\Customer\TransaksiController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokoController;
use App\Http\Controllers\Api\Umkm\AnalitikController as UmkmAnalitik;
use App\Http\Controllers\Api\Umkm\DashboardController as UmkmDashboard;
use App\Http\Controllers\Api\Umkm\PengeluaranController as UmkmPengeluaran;
use App\Http\Controllers\Api\Umkm\PrediksiController as UmkmPrediksi;
use App\Http\Controllers\Api\Umkm\ProdukController as UmkmProduk;
use App\Http\Controllers\Api\Umkm\ProfilController as UmkmProfil;
use App\Http\Controllers\Api\Umkm\SaldoController as UmkmSaldo;
use App\Http\Controllers\Api\Umkm\StokController as UmkmStok;
use App\Http\Controllers\Api\Umkm\TransaksiController as UmkmTransaksi;
use App\Http\Controllers\Api\Umkm\LaporanController as UmkmLaporan;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/toko', [TokoController::class, 'index']);
Route::get('/toko/{umkm}', [TokoController::class, 'show']);
Route::get('/produk', [ProdukController::class, 'index']);
Route::get('/produk/{produk}', [ProdukController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::patch('/profile', [ProfileController::class, 'update']);
});

Route::middleware('auth:sanctum')->prefix('master')->group(function () {
    Route::get('/jenis-usaha', [MasterController::class, 'jenisUsaha']);
    Route::get('/bank', [MasterController::class, 'bank']);
    Route::get('/kategori-produk', [MasterController::class, 'kategoriProduk']);
    Route::get('/jenis-pengeluaran', [MasterController::class, 'jenisPengeluaran']);
});

Route::middleware(['auth:sanctum', 'role:umkm'])->prefix('umkm')->group(function () {
    Route::get('/dashboard', [UmkmDashboard::class, 'index']);
    Route::get('/profil', [UmkmProfil::class, 'show']);
    Route::put('/profil', [UmkmProfil::class, 'update']);
    Route::post('/profil/rekening', [UmkmProfil::class, 'storeRekening']);
    Route::delete('/profil/rekening/{rekening}', [UmkmProfil::class, 'destroyRekening']);
    Route::get('/produk', [UmkmProduk::class, 'index']);
    Route::post('/produk', [UmkmProduk::class, 'store']);
    Route::get('/produk/{produk}', [UmkmProduk::class, 'show']);
    Route::put('/produk/{produk}', [UmkmProduk::class, 'update']);
    Route::delete('/produk/{produk}', [UmkmProduk::class, 'destroy']);
    Route::patch('/produk/{produk}/toggle', [UmkmProduk::class, 'toggleStatus']);
    Route::post('/produk/{produk}/stok', [UmkmStok::class, 'store']);
    Route::delete('/stok/{stok}', [UmkmStok::class, 'destroy']);
    Route::get('/transaksi', [UmkmTransaksi::class, 'index']);
    Route::get('/transaksi/{transaksi}', [UmkmTransaksi::class, 'show']);
    Route::post('/transaksi/{transaksi}/verifikasi', [UmkmTransaksi::class, 'verifikasi']);
    Route::post('/transaksi/{transaksi}/tolak', [UmkmTransaksi::class, 'tolak']);
    Route::post('/transaksi/{transaksi}/kirim', [UmkmTransaksi::class, 'kirim']);
    Route::get('/saldo', [UmkmSaldo::class, 'index']);
    Route::post('/saldo', [UmkmSaldo::class, 'store']);
    Route::delete('/saldo/{saldo}', [UmkmSaldo::class, 'destroy']);
    Route::get('/pengeluaran', [UmkmPengeluaran::class, 'index']);
    Route::post('/pengeluaran', [UmkmPengeluaran::class, 'store']);
    Route::get('/pengeluaran/{pengeluaran}', [UmkmPengeluaran::class, 'show']);
    Route::delete('/pengeluaran/{pengeluaran}', [UmkmPengeluaran::class, 'destroy']);
    Route::get('/analitik/tren', [UmkmAnalitik::class, 'tren']);
    Route::get('/analitik/produk-terlaris', [UmkmAnalitik::class, 'produkTerlaris']);
    Route::get('/analitik/pelanggan', [UmkmAnalitik::class, 'pelanggan']);
    Route::get('/prediksi/omzet', [UmkmPrediksi::class, 'omzet']);
    Route::get('/prediksi/stok-habis', [UmkmPrediksi::class, 'stokHabis']);
    Route::get('/prediksi/produk-trending', [UmkmPrediksi::class, 'produkTrending']);
    Route::get('/laporan/laba-rugi', [UmkmLaporan::class, 'labaRugi']);
    Route::get('/laporan/pendapatan', [UmkmLaporan::class, 'pendapatan']);
    Route::get('/laporan/perubahan-modal', [UmkmLaporan::class, 'perubahanModal']);
});

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index']);
    Route::post('/keranjang/{produk}', [KeranjangController::class, 'store']);
    Route::patch('/keranjang/{keranjang}', [KeranjangController::class, 'update']);
    Route::delete('/keranjang/{keranjang}', [KeranjangController::class, 'destroy']);
    Route::get('/checkout/{umkm}', [CheckoutController::class, 'show']);
    Route::post('/checkout/{umkm}', [CheckoutController::class, 'store']);
    Route::get('/transaksi', [TransaksiController::class, 'index']);
    Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show']);
    Route::post('/transaksi/{transaksi}/bukti', [TransaksiController::class, 'uploadBukti']);
    Route::post('/transaksi/{transaksi}/terima', [TransaksiController::class, 'terima']);
});
