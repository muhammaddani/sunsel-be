<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('post_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_name')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('post_documents');
    }
};