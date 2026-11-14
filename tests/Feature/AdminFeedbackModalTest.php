<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFeedbackModalTest extends TestCase
{
    use RefreshDatabase;

    private function authenticateAdmin(): User
    {
        $token = 'test-token-feedback';
        $user = User::factory()->create([
            'remember_token' => $token,
        ]);

        $this->actingAs($user)->withCookie('admin_auth_token', $token);

        return $user;
    }

    public function test_feedback_modal_is_rendered_and_hidden_by_default(): void
    {
        $this->authenticateAdmin();

        $response = $this->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('id="modal-feedback-status"', false);
        $response->assertDontSee('id="modal-feedback-status" class="admin-modal-overlay is-open"', false);
    }

    public function test_feedback_modal_opens_automatically_on_session_success(): void
    {
        $this->authenticateAdmin();

        $response = $this->withSession(['success' => 'Berhasil Menyimpan Perubahan Data!'])
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('admin-modal-overlay is-open', false);
        $response->assertSee('Berhasil Menyimpan Perubahan Data!');
        $response->assertSee('Berhasil!');
    }

    public function test_feedback_modal_opens_automatically_on_session_error(): void
    {
        $this->authenticateAdmin();

        $response = $this->withSession(['error' => 'Gagal Menghapus Data Pesanan'])
            ->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee('admin-modal-overlay is-open', false);
        $response->assertSee('Gagal Menghapus Data Pesanan');
        $response->assertSee('Terjadi Kendala');
    }

    public function test_admin_schedule_calendar_has_interactive_date_selection_attributes(): void
    {
        $this->authenticateAdmin();

        $response = $this->get('/admin/jadwal');

        $response->assertStatus(200);
        $response->assertSee('cal-day-interactive');
        $response->assertSee('data-date="', false);
        $response->assertSee('id="sidebar-slot-title"', false);
        $response->assertSee('id="btn-reset-calendar-filter"', false);
    }
}
