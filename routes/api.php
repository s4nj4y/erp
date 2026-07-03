<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Customer\CheckoutController;
use App\Http\Controllers\Api\Customer\KeranjangController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\TokoController;
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

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('/keranjang', [KeranjangController::class, 'index']);
    Route::post('/keranjang/{produk}', [KeranjangController::class, 'store']);
    Route::patch('/keranjang/{keranjang}', [KeranjangController::class, 'update']);
    Route::delete('/keranjang/{keranjang}', [KeranjangController::class, 'destroy']);
    Route::get('/checkout/{umkm}', [CheckoutController::class, 'show']);
    Route::post('/checkout/{umkm}', [CheckoutController::class, 'store']);
});
