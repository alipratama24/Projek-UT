<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_code')->unique(); // CH-CONTOH-001
            $table->string('invoice_number')->unique(); // INV-CH-2026-001
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('package_id')->nullable()->constrained('packages')->nullOnDelete();
            
            // Snapshot data pemesan saat pesan
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_whatsapp');
            $table->string('package_name');
            $table->unsignedBigInteger('total_price');

            // Detail sesi
            $table->date('session_date'); // misal 2026-10-17
            $table->string('session_time_slot'); // misal 15.30–17.00 WIB
            $table->string('location');
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Status: 'Permintaan', 'Dikonfirmasi', 'Selesai', 'Dibatalkan', 'Diarsipkan'
            $table->string('session_status')->default('Permintaan');
            
            // Payment Status: 'Belum dibayar', 'Bukti terkirim', 'Terverifikasi', 'Perlu perbaikan', 'Refund'
            $table->string('payment_status')->default('Belum dibayar');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

