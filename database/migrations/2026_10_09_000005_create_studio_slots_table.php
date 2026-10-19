<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_slots', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            // type: 'available' (Tersedia), 'blocked' (Blokir Studio/Libur), 'booked' (Sudah Terpesan)
            $table->string('type')->default('available');
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_slots');
    }
};

