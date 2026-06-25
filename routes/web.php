<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JenisPengeluaranController;
use App\Http\Controllers\Admin\JenisUsahaController;
use App\Http\Controllers\Admin\KategoriProdukAtributController;
use App\Http\Controllers\Admin\KategoriProdukController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Umkm\DashboardController as UmkmDashboard;
use Illuminate\Support\Facades\Route;

// ---- Publik / Customer (katalog) ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
Route::get('/produk/{produk}', [HomeController::class, 'show'])->name('produk.show');

// ---- Redirect dashboard sesuai role ----
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');

// ---- Admin ----
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Master Data
    Route::resource('users', UserController::class)->except('show');
    Route::resource('bank', BankController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('jenis-usaha', JenisUsahaController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('jenis-pengeluaran', JenisPengeluaranController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('kategori-produk', KategoriProdukController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);

    // Atribut (nested di bawah kategori produk)
    Route::post('kategori-produk/{kategori_produk}/atribut', [KategoriProdukAtributController::class, 'store'])
        ->name('kategori-produk.atribut.store');
    Route::delete('atribut/{atribut}', [KategoriProdukAtributController::class, 'destroy'])
        ->name('kategori-produk.atribut.destroy');
});

// ---- UMKM ----
Route::middleware(['auth', 'role:umkm'])->prefix('umkm')->name('umkm.')->group(function () {
    Route::get('/dashboard', [UmkmDashboard::class, 'index'])->name('dashboard');
});

// ---- Profil (semua user login) ----
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
