<?php

namespace Database\Seeders;

use App\Models\JenisUsaha;
use App\Models\Umkm;
use Illuminate\Database\Seeder;

class UmkmSeeder extends Seeder
{
    /** UMKM contoh tambahan agar grid beranda terisi penuh. Produknya diisi ProdukSeeder. */
    public function run(): void
    {
        // Foto pakai URL gambar eksternal (picsum, stabil per-seed) untuk demo;
        // upload asli tetap berupa path relatif di storage.
        $stores = [
            ['Batik Tapis Sari', 'Kerajinan', 'Sukarame, Bandar Lampung',
                'Kerajinan kain tapis dan batik khas Lampung dengan motif tradisional yang dikerjakan tangan oleh pengrajin lokal.', 'batik-tapis', '081271110001'],
            ['Kopi Ulubelu', 'Kuliner', 'Tanggamus',
                'Biji kopi robusta dataran tinggi Ulubelu, dipanggang segar setiap minggu.', 'kopi-ulubelu', '081271110002'],
            ['Sambal Lampung Bu Tin', 'Kuliner', 'Kedaton, Bandar Lampung',
                'Aneka sambal botolan tahan lama: sambal terasi, sambal teri, dan sambal cumi.', 'sambal-lampung', '081271110003'],
            ['Anyaman Rotan Jaya', 'Kerajinan', 'Metro',
                'Furnitur dan dekorasi rumah berbahan rotan alami ramah lingkungan.', 'rotan-jaya', '081271110004'],
            ['Madu Hutan Way Kambas', 'Pertanian', 'Lampung Timur',
                'Madu hutan murni hasil panen lebah liar di sekitar Taman Nasional Way Kambas.', 'madu-kambas', '081271110005'],
            ['Keripik Singkong Renyah', 'Kuliner', 'Pringsewu',
                'Camilan keripik singkong aneka rasa: original, balado, dan keju.', 'keripik-singkong', '081271110006'],
        ];

        foreach ($stores as [$nama, $jenis, $alamat, $deskripsi, $fotoSeed, $noWa]) {
            $jenisId = JenisUsaha::firstOrCreate(['nama_usaha' => $jenis])->id;

            Umkm::updateOrCreate(
                ['nama_umkm' => $nama],
                [
                    'alamat' => $alamat,
                    'deskripsi' => $deskripsi,
                    'foto' => "https://picsum.photos/seed/{$fotoSeed}/800/450",
                    'no_wa' => $noWa,
                    'jenis_usaha_id' => $jenisId,
                    'status' => true,
                ]
            );
        }
    }
}
