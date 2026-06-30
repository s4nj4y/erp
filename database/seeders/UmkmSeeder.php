<?php

namespace Database\Seeders;

use App\Models\JenisUsaha;
use App\Models\KategoriProduk;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

class UmkmSeeder extends Seeder
{
    /** UMKM contoh tambahan agar grid beranda terisi penuh. Idempotent via firstOrCreate. */
    public function run(): void
    {
        $kategori = KategoriProduk::firstOrCreate(['nama' => 'Makanan'])->id;

        // Foto pakai URL gambar eksternal (picsum, stabil per-seed) untuk demo;
        // upload asli tetap berupa path relatif di storage.
        $stores = [
            ['Batik Tapis Sari', 'Kerajinan', 'Sukarame, Bandar Lampung',
                'Kerajinan kain tapis dan batik khas Lampung dengan motif tradisional yang dikerjakan tangan oleh pengrajin lokal.', 3, 'batik-tapis'],
            ['Kopi Ulubelu', 'Kuliner', 'Tanggamus',
                'Biji kopi robusta dataran tinggi Ulubelu, dipanggang segar setiap minggu.', 2, 'kopi-ulubelu'],
            ['Sambal Lampung Bu Tin', 'Kuliner', 'Kedaton, Bandar Lampung',
                'Aneka sambal botolan tahan lama: sambal terasi, sambal teri, dan sambal cumi.', 4, 'sambal-lampung'],
            ['Anyaman Rotan Jaya', 'Kerajinan', 'Metro',
                'Furnitur dan dekorasi rumah berbahan rotan alami ramah lingkungan.', 2, 'rotan-jaya'],
            ['Madu Hutan Way Kambas', 'Pertanian', 'Lampung Timur',
                'Madu hutan murni hasil panen lebah liar di sekitar Taman Nasional Way Kambas.', 1, 'madu-kambas'],
            ['Keripik Singkong Renyah', 'Kuliner', 'Pringsewu',
                'Camilan keripik singkong aneka rasa: original, balado, dan keju.', 3, 'keripik-singkong'],
        ];

        foreach ($stores as [$nama, $jenis, $alamat, $deskripsi, $jumlahProduk, $fotoSeed]) {
            $jenisId = JenisUsaha::firstOrCreate(['nama_usaha' => $jenis])->id;

            $umkm = Umkm::updateOrCreate(
                ['nama_umkm' => $nama],
                [
                    'alamat' => $alamat,
                    'deskripsi' => $deskripsi,
                    'foto' => "https://picsum.photos/seed/{$fotoSeed}/800/450",
                    'jenis_usaha_id' => $jenisId,
                    'status' => true,
                ]
            );

            // Produk agar hitungan "n produk" tampil; lewati bila sudah ada.
            if ($umkm->produk()->count() === 0) {
                for ($i = 1; $i <= $jumlahProduk; $i++) {
                    $umkm->produk()->create([
                        'kategori_produk_id' => $kategori,
                        'nama_produk' => "$nama - Produk $i",
                        'stok' => random_int(10, 80),
                        'harga_modal' => 10000,
                        'harga' => random_int(15000, 60000),
                        'deskripsi' => 'Produk contoh dari '.$nama.'.',
                        'show' => true,
                    ]);
                }
            }
        }
    }
}
