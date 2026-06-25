<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Skema domain Informatics Business Center (IBC) — desain fresh.
 * Tabel dipertahankan dengan nama domain (Indonesia) agar mudah dikenali,
 * namun memakai PK bigint auto-increment, FK konsisten, dan timestamps standar Laravel.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ---- Master ----
        Schema::create('jenis_usaha', function (Blueprint $table) {
            $table->id();
            $table->string('nama_usaha', 100);
            $table->timestamps();
        });

        Schema::create('bank', function (Blueprint $table) {
            $table->id();
            $table->string('nama_bank', 60);
            $table->timestamps();
        });

        Schema::create('kategori_produk', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->timestamps();
        });

        Schema::create('kategori_produk_atribut', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_produk_id')->constrained('kategori_produk')->cascadeOnDelete();
            $table->string('atribut_produk', 100);
            $table->timestamps();
        });

        Schema::create('jenis_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 100);
            $table->timestamps();
        });

        // ---- UMKM (dimiliki oleh user role=umkm) ----
        Schema::create('umkm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama_umkm', 100);
            $table->string('email', 100)->nullable();
            $table->string('no_wa', 20)->nullable();
            $table->string('alamat', 150)->nullable();
            $table->string('deskripsi', 255)->nullable();
            $table->string('foto', 255)->nullable();
            $table->date('tgl_pendirian')->nullable();
            $table->string('nama_pendiri', 100)->nullable();
            $table->foreignId('jenis_usaha_id')->nullable()->constrained('jenis_usaha')->nullOnDelete();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('rekening_bank', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('bank_id')->constrained('bank')->cascadeOnDelete();
            $table->string('atas_nama', 100);
            $table->string('rekening', 60);
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // ---- Katalog produk ----
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('kategori_produk_id')->nullable()->constrained('kategori_produk')->nullOnDelete();
            $table->string('nama_produk', 100);
            $table->integer('stok')->default(0);
            $table->unsignedBigInteger('harga_modal')->default(0);
            $table->unsignedBigInteger('harga')->default(0);
            $table->text('deskripsi')->nullable();
            $table->string('berat', 100)->nullable();
            $table->string('kandungan', 100)->nullable();
            $table->string('warna', 100)->nullable();
            $table->string('bahan', 100)->nullable();
            $table->string('ukuran', 100)->nullable();
            $table->string('gambar', 150)->nullable();
            $table->boolean('show')->default(true)->comment('tampil di katalog');
            $table->timestamps();
        });

        Schema::create('produk_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->foreignId('atribut_id')->constrained('kategori_produk_atribut')->cascadeOnDelete();
            $table->string('value', 60);
            $table->timestamps();
        });

        Schema::create('stok', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->enum('status', ['masuk', 'keluar'])->default('masuk');
            $table->integer('jumlah_masuk')->default(0);
            $table->integer('jumlah_keluar')->default(0);
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });

        // ---- E-commerce ----
        Schema::create('keranjang_belanja', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->unsignedInteger('qty')->default(1);
            $table->timestamps();
        });

        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('bank_id')->nullable()->constrained('bank')->nullOnDelete();
            $table->string('kode_transaksi', 40)->unique();
            $table->dateTime('tanggal');
            $table->string('status', 30)->default('pending');
            $table->string('status_bayar', 15)->default('belum');
            $table->string('bukti_pembayaran', 200)->nullable();
            $table->timestamps();
        });

        Schema::create('transaksi_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk')->cascadeOnDelete();
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('harga')->default(0)->comment('snapshot harga saat transaksi');
            $table->timestamps();
        });

        // ---- Keuangan UMKM ----
        Schema::create('saldo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->date('tanggal_transaksi');
            $table->enum('jenis_transaksi', ['investasi_awal', 'penambahan_modal', 'pengambilan_modal']);
            $table->string('keterangan', 255)->nullable();
            $table->bigInteger('jumlah')->default(0);
            $table->bigInteger('saldo')->default(0);
            $table->timestamps();
        });

        Schema::create('keuangan_umkm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->date('tanggal_transaksi');
            $table->text('deskripsi')->nullable();
            $table->decimal('debit', 15, 2)->default(0);
            $table->decimal('kredit', 15, 2)->default(0);
            $table->decimal('saldo', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('transaksi_pengeluaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('jenis_pengeluaran_id')->nullable()->constrained('jenis_pengeluaran')->nullOnDelete();
            $table->dateTime('tanggal_pengeluaran');
            $table->unsignedBigInteger('total_harga')->default(0);
            $table->timestamps();
        });

        Schema::create('transaksi_pengeluaran_detail', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_pengeluaran_id')->constrained('transaksi_pengeluaran')->cascadeOnDelete();
            $table->string('keterangan', 200);
            $table->unsignedInteger('qty')->default(1);
            $table->unsignedBigInteger('harga')->default(0);
            $table->unsignedBigInteger('sub_total')->default(0);
            $table->timestamps();
        });

        Schema::create('transaksi_keuangan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('umkm_id')->constrained('umkm')->cascadeOnDelete();
            $table->foreignId('transaksi_pengeluaran_detail_id')->nullable()->constrained('transaksi_pengeluaran_detail')->nullOnDelete();
            $table->dateTime('tanggal_transaksi');
            $table->string('keterangan', 200)->nullable();
            $table->enum('jenis_transaksi', ['debit', 'kredit']);
            $table->bigInteger('jumlah')->default(0);
            $table->bigInteger('saldo')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi_keuangan');
        Schema::dropIfExists('transaksi_pengeluaran_detail');
        Schema::dropIfExists('transaksi_pengeluaran');
        Schema::dropIfExists('keuangan_umkm');
        Schema::dropIfExists('saldo');
        Schema::dropIfExists('transaksi_detail');
        Schema::dropIfExists('transaksi');
        Schema::dropIfExists('keranjang_belanja');
        Schema::dropIfExists('stok');
        Schema::dropIfExists('produk_detail');
        Schema::dropIfExists('produk');
        Schema::dropIfExists('rekening_bank');
        Schema::dropIfExists('umkm');
        Schema::dropIfExists('jenis_pengeluaran');
        Schema::dropIfExists('kategori_produk_atribut');
        Schema::dropIfExists('kategori_produk');
        Schema::dropIfExists('bank');
        Schema::dropIfExists('jenis_usaha');
    }
};
