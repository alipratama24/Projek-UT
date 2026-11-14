<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // potret-pribadi, cerita-keluarga, editorial-bisnis
            $table->string('code')->unique(); // PKT-CONTOH-001
            $table->string('name');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price'); // dalam rupiah (misal 9500000)
            $table->unsignedInteger('duration_minutes')->nullable(); // 45, 90, 240
            $table->string('duration')->nullable(); // '60 menit', '90 menit'
            $table->string('photo_count_label')->nullable(); // '10 foto retouched'
            $table->unsignedInteger('photo_count')->default(10); // integer count
            $table->string('image')->nullable();
            $table->string('badge')->nullable(); // 'Populer', 'Pilihan Favorit'
            $table->json('features')->nullable(); // array poin fasilitas
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
