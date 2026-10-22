<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_url_redirects_to_admin_login(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Santa Cruz, Laguna');
        $response->assertDontSee('Register an account');
    }

    public function test_web_resident_registration_routes_do_not_exist(): void
    {
        $this->get('/register')->assertStatus(404);
        $this->post('/register', [])->assertStatus(404);
    }

    public function test_resident_cannot_login_on_web_and_gets_mobile_app_message(): void
    {
        $resident = User::factory()->create([
            'role' => 'resident',
            'email' => 'maria.santos@gmail.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        $response = $this->from('/login')->post('/login', [
            'email' => $resident->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors([
            'email' => 'Residents use the mobile app.',
        ]);
        $this->assertGuest();
    }

    public function test_admin_login_redirects_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_resident_cannot_access_admin_dashboard_or_reports_and_never_redirect_loops(): void
    {
        $resident = User::factory()->create([
            'role' => 'resident',
        ]);

        // Visiting root with a stale resident session logs out and lands cleanly on /login (200) without redirect loops
        $this->actingAs($resident)
            ->followingRedirects()
            ->get('/')
            ->assertStatus(200)
            ->assertSee('Residents use the mobile app.');

        $this->assertGuest();

        // Visiting /login with a stale resident session logs out and renders /login (200) without redirect loops
        $this->actingAs($resident)
            ->followingRedirects()
            ->get('/login')
            ->assertStatus(200);

        $this->assertGuest();

        // Visiting /admin/dashboard with a stale resident session logs out and redirects to /login
        $response = $this->actingAs($resident)->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email' => 'Residents use the mobile app.']);
        $this->assertGuest();
    }

    public function test_admin_can_access_admin_portal_sections(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.reports'))->assertStatus(200);
        $this->actingAs($admin)->get(route('admin.map'))->assertStatus(200);
    }
}
