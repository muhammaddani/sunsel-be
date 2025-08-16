<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            // Perintah untuk menghapus kolom
            $table->dropColumn('published_at');
        });
    }

    public function down(): void
    {
        Schema::table('galleries', function (Blueprint $table) {
            // Perintah untuk mengembalikan kolom jika migration di-rollback
            $table->timestamp('published_at')->nullable();
        });
    }
};