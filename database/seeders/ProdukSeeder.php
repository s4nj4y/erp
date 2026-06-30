<?php

namespace Database\Seeders;

use App\Models\KategoriProduk;
use App\Models\Produk;
use App\Models\Umkm;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProdukSeeder extends Seeder
{
    /** Produk realistis yang dijual tiap UMKM. Idempotent via firstOrCreate per (toko, nama). */
    public function run(): void
    {
        $cat = fn (string $nama) => KategoriProduk::firstOrCreate(['nama' => $nama])->id;
        $makanan = $cat('Makanan');
        $kerajinan = $cat('Kerajinan');

        // [namaProduk, harga, hargaModal, stok, deskripsi, kategoriId, atribut tambahan]
        $catalog = [
            'Batik Tapis Sari' => [
                ['Kain Tapis Motif Pucuk Rebung', 350000, 220000, 15, 'Kain tapis tenun tangan motif pucuk rebung dengan benang emas.', $kerajinan, ['bahan' => 'Katun & benang emas', 'warna' => 'Merah marun', 'ukuran' => '200x100 cm']],
                ['Selendang Batik Lampung', 120000, 75000, 30, 'Selendang batik tulis khas Lampung, halus dan ringan.', $kerajinan, ['bahan' => 'Katun', 'warna' => 'Coklat']],
                ['Peci Tapis', 85000, 50000, 40, 'Peci beludru dihiasi sulam tapis emas.', $kerajinan, ['bahan' => 'Beludru & tapis', 'warna' => 'Hitam']],
            ],
            'Kopi Ulubelu' => [
                ['Kopi Robusta Bubuk 250gr', 35000, 22000, 60, 'Kopi robusta Ulubelu bubuk halus, kemasan 250 gram.', $makanan, ['berat' => '250 gr', 'kandungan' => '100% robusta']],
                ['Kopi Biji Sangrai 500gr', 65000, 45000, 40, 'Biji kopi robusta sangrai medium, kemasan 500 gram.', $makanan, ['berat' => '500 gr', 'kandungan' => '100% robusta']],
            ],
            'Sambal Lampung Bu Tin' => [
                ['Sambal Terasi Botol', 25000, 15000, 80, 'Sambal terasi pedas khas Lampung dalam botol.', $makanan, ['berat' => '200 gr']],
                ['Sambal Teri', 30000, 18000, 70, 'Sambal teri medan, gurih dan pedas.', $makanan, ['berat' => '180 gr']],
                ['Sambal Cumi', 35000, 22000, 50, 'Sambal cumi premium, cocok untuk lauk.', $makanan, ['berat' => '180 gr']],
                ['Sambal Bawang', 22000, 13000, 60, 'Sambal bawang pedas nampol.', $makanan, ['berat' => '200 gr']],
            ],
            'Anyaman Rotan Jaya' => [
                ['Kursi Rotan Minimalis', 450000, 300000, 10, 'Kursi rotan alami desain minimalis untuk ruang tamu.', $kerajinan, ['bahan' => 'Rotan alami', 'warna' => 'Natural', 'ukuran' => '60x60x80 cm']],
                ['Keranjang Rotan Serbaguna', 75000, 45000, 25, 'Keranjang rotan serbaguna untuk penyimpanan.', $kerajinan, ['bahan' => 'Rotan', 'warna' => 'Natural', 'ukuran' => '30x30x25 cm']],
            ],
            'Madu Hutan Way Kambas' => [
                ['Madu Hutan Murni 500ml', 95000, 65000, 35, 'Madu hutan murni hasil panen lebah liar, tanpa campuran.', $makanan, ['berat' => '500 ml', 'kandungan' => 'Madu hutan 100%']],
            ],
            'Keripik Singkong Renyah' => [
                ['Keripik Singkong Original 200gr', 15000, 9000, 90, 'Keripik singkong renyah rasa original.', $makanan, ['berat' => '200 gr']],
                ['Keripik Singkong Balado', 17000, 10000, 80, 'Keripik singkong bumbu balado pedas manis.', $makanan, ['berat' => '200 gr']],
                ['Keripik Singkong Keju', 18000, 11000, 75, 'Keripik singkong taburan keju gurih.', $makanan, ['berat' => '200 gr']],
            ],
        ];

        foreach ($catalog as $namaUmkm => $produkList) {
            $umkm = Umkm::where('nama_umkm', $namaUmkm)->first();
            if (! $umkm) {
                continue; // toko belum di-seed
            }

            foreach ($produkList as [$nama, $harga, $modal, $stok, $deskripsi, $kategoriId, $atribut]) {
                Produk::firstOrCreate(
                    ['umkm_id' => $umkm->id, 'nama_produk' => $nama],
                    array_merge([
                        'kategori_produk_id' => $kategoriId,
                        'harga' => $harga,
                        'harga_modal' => $modal,
                        'stok' => $stok,
                        'deskripsi' => $deskripsi,
                        'gambar' => 'https://picsum.photos/seed/'.Str::slug($nama).'/600/600',
                        'show' => true,
                    ], $atribut)
                );
            }
        }
    }
}
