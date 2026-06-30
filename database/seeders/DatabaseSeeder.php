<?php

namespace Database\Seeders;

use App\Models\Bank;
use App\Models\JenisPengeluaran;
use App\Models\JenisUsaha;
use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Umkm;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Akun demo per role ----
        $admin = User::create([
            'name' => 'Administrator',
            'username' => 'admin',
            'email' => 'admin@ibc.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'status' => true,
        ]);

        $umkmUser = User::create([
            'name' => 'Pemilik UMKM',
            'username' => 'umkm',
            'email' => 'umkm@ibc.test',
            'password' => Hash::make('password'),
            'role' => 'umkm',
            'status' => true,
        ]);

        User::create([
            'name' => 'Pelanggan',
            'username' => 'customer',
            'email' => 'customer@ibc.test',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'status' => true,
        ]);

        // ---- Data referensi ----
        Bank::insert([
            ['nama_bank' => 'BCA', 'created_at' => now(), 'updated_at' => now()],
            ['nama_bank' => 'BRI', 'created_at' => now(), 'updated_at' => now()],
            ['nama_bank' => 'Mandiri', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $kuliner = JenisUsaha::create(['nama_usaha' => 'Kuliner']);
        JenisUsaha::create(['nama_usaha' => 'Kerajinan']);

        foreach (['Bahan Baku', 'Operasional', 'Gaji', 'Transportasi', 'Lainnya'] as $jp) {
            JenisPengeluaran::create(['nama' => $jp]);
        }

        $makanan = KategoriProduk::create(['nama' => 'Makanan']);
        KategoriProduk::create(['nama' => 'Minuman']);

        // ---- UMKM + produk contoh ----
        $umkm = Umkm::create([
            'user_id' => $umkmUser->id,
            'nama_umkm' => 'Dapur Lampung',
            'email' => 'dapur@ibc.test',
            'no_wa' => '081234567890',
            'alamat' => 'Bandar Lampung',
            'deskripsi' => 'UMKM kuliner khas Lampung',
            'foto' => 'https://picsum.photos/seed/dapur-lampung/800/450',
            'tgl_pendirian' => '2023-01-01',
            'nama_pendiri' => 'Pemilik UMKM',
            'jenis_usaha_id' => $kuliner->id,
            'status' => true,
        ]);

        Produk::insert([
            [
                'umkm_id' => $umkm->id, 'kategori_produk_id' => $makanan->id,
                'nama_produk' => 'Keripik Pisang', 'stok' => 50, 'harga_modal' => 8000, 'harga' => 15000,
                'deskripsi' => 'Keripik pisang renyah khas Lampung.', 'show' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'umkm_id' => $umkm->id, 'kategori_produk_id' => $makanan->id,
                'nama_produk' => 'Kopi Robusta Lampung', 'stok' => 30, 'harga_modal' => 25000, 'harga' => 45000,
                'deskripsi' => 'Kopi robusta asli Lampung 200gr.', 'show' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        // Rekening bank UMKM (agar checkout langsung bisa)
        $umkm->rekening()->create([
            'bank_id' => \App\Models\Bank::where('nama_bank', 'BRI')->first()->id,
            'atas_nama' => 'Dapur Lampung',
            'rekening' => '1234567890',
            'status' => true,
        ]);

        // UMKM contoh tambahan agar grid beranda terisi penuh.
        $this->call(UmkmSeeder::class);
    }
}
