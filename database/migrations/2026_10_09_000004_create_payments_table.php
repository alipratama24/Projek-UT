<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_code')->unique(); // BYR-CONTOH-001
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            
            $table->string('sender_name');
            $table->date('transfer_date');
            $table->string('bank_source')->nullable(); // Bank BCA, dsb.
            $table->unsignedBigInteger('nominal'); // nominal diajukan
            $table->text('sender_notes')->nullable();
            
            // Bukti file
            $table->string('proof_file')->nullable(); // path storage
            $table->string('file_original_name')->nullable();
            $table->string('file_extension')->default('PDF');
            
            // Status bukti: 'Bukti terkirim', 'Terverifikasi', 'Perlu perbaikan', 'Ditolak'
            $table->string('status')->default('Bukti terkirim');
            $table->text('internal_review_notes')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

