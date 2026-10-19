<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('customer_code')->unique(); // PLG-CONTOH-001
            $table->string('name');
            $table->string('email');
            $table->string('whatsapp');
            $table->text('notes')->nullable();
            $table->string('status')->default('Aktif'); // Aktif, Diarsipkan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

