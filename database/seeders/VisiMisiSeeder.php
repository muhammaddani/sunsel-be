<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\VisiMisi;

class VisiMisiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data lama untuk menghindari duplikasi
        VisiMisi::truncate();

        // Buat satu Visi
        VisiMisi::create(['type' => 'visi', 'content' => 'Isi Visi Nagari Anda di sini.']);

        // Buat beberapa Misi
        VisiMisi::create(['type' => 'misi', 'content' => 'Isi Misi pertama Anda di sini.']);
        VisiMisi::create(['type' => 'misi', 'content' => 'Isi Misi kedua Anda di sini.']);
        VisiMisi::create(['type' => 'misi', 'content' => 'Isi Misi ketiga Anda di sini.']);
    }
}
