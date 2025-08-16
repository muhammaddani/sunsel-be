<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Perintah untuk MENGHAPUS 'agenda' dari pilihan enum
        DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('berita', 'pengumuman', 'wisata') NOT NULL");
    }

    public function down(): void
    {
        // Perintah untuk MENGEMBALIKAN 'agenda' jika migrasi di-rollback
        DB::statement("ALTER TABLE posts MODIFY COLUMN type ENUM('berita', 'pengumuman', 'agenda') NOT NULL");
    }
};