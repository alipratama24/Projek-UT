<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Package;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_download_real_pdf_invoice_from_checkout(): void
    {
        $order = Order::create([
            'order_code' => 'CH-TEST-INV-001',
            'invoice_number' => 'INV-CH-2026-999',
            'package_name' => 'Graduation',
            'customer_name' => 'Adam Permana',
            'customer_email' => 'adpstore@mail.com',
            'customer_whatsapp' => '08123456789',
            'total_price' => 1500000,
            'session_date' => '2026-10-17',
            'session_time_slot' => '15.30–17.00 WIB · 90 menit',
            'location' => 'PuloJahe',
            'payment_status' => 'Belum dibayar',
            'session_status' => 'Permintaan',
        ]);

        // Request URL download invoice PDF
        $response = $this->get('/checkout/unduh-invoice?order='.$order->order_code);

        $response->assertStatus(200);

        // Header harus berupa berkas PDF
        $response->assertHeader('content-type', 'application/pdf');

        // Content-Disposition harus menyertakan nama file invoice
        $disposition = $response->headers->get('content-disposition');
        $this->assertStringContainsString('Invoice-inv-ch-2026-999.pdf', $disposition);
    }
}
