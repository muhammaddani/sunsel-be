<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('penduduks', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique();
            $table->string('no_kk', 16);
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->date('tanggal_lahir');

            $table->foreignId('jorong_id')->constrained('jorongs')->onDelete('cascade');
            $table->foreignId('agama_id')->constrained('agamas')->onDelete('cascade');
            $table->foreignId('pekerjaan_id')->constrained('pekerjaans')->onDelete('cascade');
            $table->foreignId('pendidikan_terakhir_id')->constrained('pendidikan_terakhirs')->onDelete('cascade');
            $table->foreignId('pendidikan_ditempuh_id')->constrained('pendidikan_ditempuhs')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penduduks');
    }
};
