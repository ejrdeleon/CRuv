<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Barangay San Isidro');
    }

    public function test_resident_can_register_and_is_redirected_to_resident_home(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test Resident',
            'email' => 'resident.test@example.com',
            'phone' => '0917-123-4567',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Purok 1',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $response->assertRedirect(route('resident.home'));
        $this->assertAuthenticated();

        $user = User::where('email', 'resident.test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('resident', $user->role);
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
    }

    public function test_resident_cannot_access_admin_dashboard(): void
    {
        $resident = User::factory()->create([
            'role' => 'resident',
        ]);

        $response = $this->actingAs($resident)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_staff_can_access_admin_reports_but_not_admin_staff_management(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        // Staff CAN access reports management
        $responseReports = $this->actingAs($staff)->get(route('admin.reports'));
        $responseReports->assertStatus(200);

        // Staff CANNOT access admin staff management
        $responseStaff = $this->actingAs($staff)->get(route('admin.staff'));
        $responseStaff->assertStatus(403);
    }
}
