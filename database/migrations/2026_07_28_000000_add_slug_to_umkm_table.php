<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->string('slug', 120)->nullable()->unique()->after('nama_umkm');
        });

        // Backfill toko lama; tabrakan nama diberi akhiran -2, -3, dst.
        foreach (DB::table('umkm')->orderBy('id')->get(['id', 'nama_umkm']) as $row) {
            $base = Str::slug($row->nama_umkm) ?: 'toko';
            $slug = $base;
            $i = 2;
            while (DB::table('umkm')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            DB::table('umkm')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('umkm', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
