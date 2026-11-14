<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCustomerTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-customer-token-123';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_admin_customer_index_displays_customers_with_detail_links(): void
    {
        $this->authenticateAdmin();

        $customer = Customer::create([
            'customer_code' => 'PLG-2026-001',
            'name' => 'Ayu Pramesti',
            'email' => 'ayu@example.com',
            'whatsapp' => '081234567890',
            'status' => 'aktif',
        ]);

        $response = $this->get('/admin/pelanggan');

        $response->assertStatus(200);
        $response->assertSee('Ayu Pramesti');
        $response->assertSee('PLG-2026-001');
        $response->assertSee(route('admin.pelanggan.detail', ['id' => 'PLG-2026-001']));
    }

    public function test_admin_customer_detail_shows_profile_and_order_history(): void
    {
        $this->authenticateAdmin();

        $customer = Customer::create([
            'customer_code' => 'PLG-2026-002',
            'name' => 'Bima Prasetya',
            'email' => 'bima@example.com',
            'whatsapp' => '081298765432',
            'notes' => 'Menyukai konsep potret natural tanpa properti berlebihan.',
            'status' => 'aktif',
        ]);

        $order = Order::create([
            'order_code' => 'CH-BIMA-001',
            'invoice_number' => 'INV-CH-BIMA-001',
            'customer_id' => $customer->id,
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'customer_whatsapp' => $customer->whatsapp,
            'package_name' => 'Graduation',
            'total_price' => 1500000,
            'location' => 'Studio Utama naro capture',
            'session_status' => 'Selesai',
            'payment_status' => 'Terverifikasi',
            'session_date' => '2026-11-20',
            'session_time_slot' => '10.00–11.30 WIB',
        ]);

        $response = $this->get('/admin/pelanggan/PLG-2026-002');

        $response->assertStatus(200);
        $response->assertSee('Bima Prasetya');
        $response->assertSee('PLG-2026-002');
        $response->assertSee('Menyukai konsep potret natural');
        $response->assertSee('CH-BIMA-001');
        $response->assertSee('Graduation');
        $response->assertSee('Rp1.500.000');
        $response->assertSee('Riwayat Pesanan &amp; Sesi Foto', false);
    }

    public function test_admin_can_update_customer_profile(): void
    {
        $this->authenticateAdmin();

        $customer = Customer::create([
            'customer_code' => 'PLG-2026-003',
            'name' => 'Citra Dewi',
            'email' => 'citra@example.com',
            'whatsapp' => '081211112222',
            'status' => 'aktif',
        ]);

        $response = $this->put('/admin/pelanggan/PLG-2026-003', [
            'name' => 'Citra Dewi Updated',
            'email' => 'citra.new@example.com',
            'whatsapp' => '081299990000',
            'status' => 'aktif',
            'notes' => 'Catatan diperbarui.',
        ]);

        $response->assertRedirect('/admin/pelanggan/PLG-2026-003');

        $customer->refresh();
        $this->assertEquals('Citra Dewi Updated', $customer->name);
        $this->assertEquals('citra.new@example.com', $customer->email);
        $this->assertEquals('Catatan diperbarui.', $customer->notes);
    }

    public function test_admin_can_toggle_customer_archive_status(): void
    {
        $this->authenticateAdmin();

        $customer = Customer::create([
            'customer_code' => 'PLG-2026-004',
            'name' => 'Doni Darmawan',
            'email' => 'doni@example.com',
            'whatsapp' => '081233334444',
            'status' => 'aktif',
        ]);

        $response = $this->post('/admin/pelanggan/PLG-2026-004/toggle');
        $response->assertRedirect('/admin/pelanggan/PLG-2026-004');

        $customer->refresh();
        $this->assertEquals('diarsipkan', $customer->status);

        $response2 = $this->post('/admin/pelanggan/PLG-2026-004/toggle');
        $customer->refresh();
        $this->assertEquals('aktif', $customer->status);
    }
}
