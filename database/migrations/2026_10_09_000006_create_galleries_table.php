<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('galleries', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('client_name');
            $table->string('cover_image')->nullable();
            $table->unsignedInteger('target_photos')->default(20);
            // status: 'Draf', 'Penyuntingan', 'Siap', 'Diterbitkan'
            $table->string('status')->default('Draf');
            // access: 'Privat', 'Terbuka'
            $table->string('access_type')->default('Privat');
            $table->string('pin_code')->nullable();
            $table->timestamps();
        });

        Schema::create('gallery_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gallery_id')->constrained('galleries')->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gallery_photos');
        Schema::dropIfExists('galleries');
    }
};

