<?php

use App\Http\Controllers\Admin\BankController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard;
use App\Http\Controllers\Admin\JenisPengeluaranController;
use App\Http\Controllers\Admin\JenisUsahaController;
use App\Http\Controllers\Admin\KategoriProdukAtributController;
use App\Http\Controllers\Admin\KategoriProdukController;
use App\Http\Controllers\Admin\ProdukController;
use App\Http\Controllers\Admin\RekeningBankController;
use App\Http\Controllers\Admin\StokController;
use App\Http\Controllers\Admin\UmkmController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Customer\CartController;
use App\Http\Controllers\Customer\CheckoutController;
use App\Http\Controllers\Customer\HomeController;
use App\Http\Controllers\Customer\TransaksiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Umkm\DashboardController as UmkmDashboard;
use App\Http\Controllers\Umkm\LaporanController as UmkmLaporan;
use App\Http\Controllers\Umkm\PengeluaranController as UmkmPengeluaran;
use App\Http\Controllers\Umkm\ProdukController as UmkmProduk;
use App\Http\Controllers\Umkm\SaldoController as UmkmSaldo;
use App\Http\Controllers\Umkm\ProfilController as UmkmProfil;
use App\Http\Controllers\Umkm\StokController as UmkmStok;
use App\Http\Controllers\Umkm\TransaksiController as UmkmTransaksi;
use Illuminate\Support\Facades\Route;

// ---- Publik / Customer (katalog) ----
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
Route::get('/toko/{umkm}', [HomeController::class, 'toko'])->name('toko.show');
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

    // UMKM + Rekening
    Route::resource('umkm', UmkmController::class)->except('show');
    Route::patch('umkm/{umkm}/toggle', [UmkmController::class, 'toggleStatus'])->name('umkm.toggle');
    Route::post('umkm/{umkm}/rekening', [RekeningBankController::class, 'store'])->name('umkm.rekening.store');
    Route::delete('rekening/{rekening}', [RekeningBankController::class, 'destroy'])->name('umkm.rekening.destroy');

    // Produk + Stok
    Route::resource('produk', ProdukController::class)->except('show');
    Route::patch('produk/{produk}/toggle', [ProdukController::class, 'toggleStatus'])->name('produk.toggle');
    Route::post('produk/{produk}/stok', [StokController::class, 'store'])->name('produk.stok.store');
    Route::delete('stok/{stok}', [StokController::class, 'destroy'])->name('produk.stok.destroy');
});

// ---- UMKM ----
Route::middleware(['auth', 'role:umkm'])->prefix('umkm')->name('umkm.')->group(function () {
    Route::get('/dashboard', [UmkmDashboard::class, 'index'])->name('dashboard');

    // Profil & Rekening
    Route::get('/profil', [UmkmProfil::class, 'edit'])->name('profil.edit');
    Route::put('/profil', [UmkmProfil::class, 'update'])->name('profil.update');
    Route::post('/profil/rekening', [UmkmProfil::class, 'storeRekening'])->name('profil.rekening.store');
    Route::delete('/profil/rekening/{rekening}', [UmkmProfil::class, 'destroyRekening'])->name('profil.rekening.destroy');

    // Produk + Stok (toko sendiri)
    Route::resource('produk', UmkmProduk::class)->except('show');
    Route::patch('produk/{produk}/toggle', [UmkmProduk::class, 'toggleStatus'])->name('produk.toggle');
    Route::post('produk/{produk}/stok', [UmkmStok::class, 'store'])->name('produk.stok.store');
    Route::delete('stok/{stok}', [UmkmStok::class, 'destroy'])->name('produk.stok.destroy');

    // Pesanan masuk + verifikasi
    Route::get('/transaksi', [UmkmTransaksi::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{transaksi}', [UmkmTransaksi::class, 'show'])->name('transaksi.show');
    Route::post('/transaksi/{transaksi}/verifikasi', [UmkmTransaksi::class, 'verifikasi'])->name('transaksi.verifikasi');
    Route::post('/transaksi/{transaksi}/tolak', [UmkmTransaksi::class, 'tolak'])->name('transaksi.tolak');
    Route::post('/transaksi/{transaksi}/kirim', [UmkmTransaksi::class, 'kirim'])->name('transaksi.kirim');

    // Keuangan: Saldo/Modal
    Route::get('/saldo', [UmkmSaldo::class, 'index'])->name('saldo.index');
    Route::post('/saldo', [UmkmSaldo::class, 'store'])->name('saldo.store');
    Route::delete('/saldo/{saldo}', [UmkmSaldo::class, 'destroy'])->name('saldo.destroy');

    // Keuangan: Pengeluaran
    Route::get('/pengeluaran', [UmkmPengeluaran::class, 'index'])->name('pengeluaran.index');
    Route::get('/pengeluaran/create', [UmkmPengeluaran::class, 'create'])->name('pengeluaran.create');
    Route::post('/pengeluaran', [UmkmPengeluaran::class, 'store'])->name('pengeluaran.store');
    Route::get('/pengeluaran/{pengeluaran}', [UmkmPengeluaran::class, 'show'])->name('pengeluaran.show');
    Route::delete('/pengeluaran/{pengeluaran}', [UmkmPengeluaran::class, 'destroy'])->name('pengeluaran.destroy');

    // Laporan (+ export pdf/excel via ?export=)
    Route::get('/laporan/laba-rugi', [UmkmLaporan::class, 'labaRugi'])->name('laporan.laba-rugi');
    Route::get('/laporan/pendapatan', [UmkmLaporan::class, 'pendapatan'])->name('laporan.pendapatan');
    Route::get('/laporan/perubahan-modal', [UmkmLaporan::class, 'perubahanModal'])->name('laporan.perubahan-modal');
});

// ---- Customer: keranjang, checkout, transaksi ----
Route::middleware(['auth', 'role:customer'])->group(function () {
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::post('/keranjang/{produk}', [CartController::class, 'store'])->name('cart.store');
    Route::patch('/keranjang/{keranjang}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/keranjang/{keranjang}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::get('/checkout/{umkm}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('/checkout/{umkm}', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/transaksi', [TransaksiController::class, 'index'])->name('transaksi.index');
    Route::get('/transaksi/{transaksi}', [TransaksiController::class, 'show'])->name('transaksi.show');
    Route::post('/transaksi/{transaksi}/bukti', [TransaksiController::class, 'uploadBukti'])->name('transaksi.bukti');
    Route::post('/transaksi/{transaksi}/terima', [TransaksiController::class, 'terima'])->name('transaksi.terima');
    Route::get('/transaksi/{transaksi}/invoice', [TransaksiController::class, 'invoice'])->name('transaksi.invoice');
});

// ---- Profil (semua user login) ----
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
