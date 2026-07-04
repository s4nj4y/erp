<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\KeranjangController;
use App\Http\Controllers\Api\Customer\TransaksiController;
use App\Http\Controllers\Api\MasterController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokoController;
use App\Http\Controllers\Api\Umkm\DashboardController as UmkmDashboard;
use App\Http\Controllers\Api\Umkm\ProfilController as UmkmProfil;
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
});

Route::middleware(['auth:sanctum', 'role:umkm'])->prefix('umkm')->group(function () {
    Route::get('/dashboard', [UmkmDashboard::class, 'index']);
    Route::get('/profil', [UmkmProfil::class, 'show']);
    Route::put('/profil', [UmkmProfil::class, 'update']);
    Route::post('/profil/rekening', [UmkmProfil::class, 'storeRekening']);
    Route::delete('/profil/rekening/{rekening}', [UmkmProfil::class, 'destroyRekening']);
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
