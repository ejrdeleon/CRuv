<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Livewire\Livewire;
use Tests\TestCase;

class ApiV1WorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected ReportCategory $roadCategory;

    protected ReportCategory $hazardCategory;

    protected function setUp(): void
    {
        parent::setUp();

        $dept = Department::create([
            'name' => 'Barangay Public Works',
            'code' => 'BPWE',
            'is_active' => true,
        ]);

        $this->roadCategory = ReportCategory::create([
            'name' => 'Damaged Roads & Potholes',
            'slug' => 'damaged-roads',
            'default_priority' => 'medium',
            'department_id' => $dept->id,
            'is_hazardous' => false,
            'is_active' => true,
        ]);

        $this->hazardCategory = ReportCategory::create([
            'name' => 'Fallen Electrical Lines',
            'slug' => 'electrical-lines',
            'default_priority' => 'urgent',
            'department_id' => $dept->id,
            'is_hazardous' => true,
            'safety_advisory' => 'Keep at least 10 meters away from fallen lines.',
            'is_active' => true,
        ]);
    }

    public function test_resident_can_register_login_and_logout_via_api(): void
    {
        $registerResponse = $this->postJson('/api/v1/register', [
            'name' => 'Juan Dela Cruz',
            'email' => 'juan.api@example.com',
            'phone' => '0917-555-0199',
            'barangay' => 'Barangay San Isidro',
            'address' => 'Purok 4, Mabini St.',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'device_name' => 'pixel-8',
        ]);

        $registerResponse->assertCreated()
            ->assertJsonStructure([
                'message',
                'token',
                'user' => ['id', 'name', 'email', 'phone', 'role', 'barangay'],
            ])
            ->assertJsonPath('user.role', 'resident');

        $loginResponse = $this->postJson('/api/v1/login', [
            'email' => 'juan.api@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk()
            ->assertJsonStructure(['message', 'token', 'user']);

        $token = $loginResponse->json('token');

        $logoutResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/logout');

        $logoutResponse->assertOk()
            ->assertJsonPath('message', 'Signed out successfully.');
    }

    public function test_unauthenticated_requests_return_consistent_401_json(): void
    {
        $this->getJson('/api/v1/profile')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Unauthenticated. Please sign in to continue.');

        $this->getJson('/api/v1/my-reports')
            ->assertUnauthorized();
    }

    public function test_categories_endpoint_returns_active_categories(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Damaged Roads & Potholes');
    }

    public function test_resident_can_submit_report_with_photo_resizing_and_coordinates(): void
    {
        Storage::fake('public');

        $resident = User::factory()->create([
            'role' => 'resident',
            'barangay' => 'Barangay San Isidro',
        ]);

        Sanctum::actingAs($resident);

        // Create a 2000x1500 image to verify automatic resizing down to <= 1600px
        $oversizedPhoto = UploadedFile::fake()->image('pothole.jpg', 2000, 1500);

        $response = $this->postJson('/api/v1/reports', [
            'category_id' => $this->roadCategory->id,
            'title' => 'Deep pothole near elementary school',
            'description' => 'Large pothole filled with rainwater causing hazard to tricycles and pedestrians.',
            'priority' => 'high',
            'latitude' => 14.1819,
            'longitude' => 121.2852,
            'address_text' => 'Purok 2, Rizal Avenue',
            'landmark' => 'In front of Elementary School Gate',
            'photo' => $oversizedPhoto,
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.title', 'Deep pothole near elementary school')
            ->assertJsonPath('data.status', 'submitted')
            ->assertJsonPath('data.latitude', 14.1819)
            ->assertJsonCount(1, 'data.images');

        $report = Report::with('images')->firstOrFail();
        $storedFilePath = $report->images->first()->file_path;

        Storage::disk('public')->assertExists($storedFilePath);

        $rawBytes = Storage::disk('public')->get($storedFilePath);
        $img = @imagecreatefromstring((string) $rawBytes);
        if ($img !== false) {
            $this->assertLessThanOrEqual(1600, imagesx($img));
            $this->assertLessThanOrEqual(1600, imagesy($img));
            imagedestroy($img);
        }
    }

    public function test_hazardous_category_requires_safety_acknowledgement(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);
        Sanctum::actingAs($resident);

        $response = $this->postJson('/api/v1/reports', [
            'category_id' => $this->hazardCategory->id,
            'title' => 'Fallen live electric cable on sidewalk',
            'description' => 'Sparking wire hanging low across the street after strong wind.',
            'latitude' => 14.1815,
            'longitude' => 121.2845,
            'address_text' => 'Purok 3, Burgos St.',
            'is_hazard_warning_acknowledged' => false,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['is_hazard_warning_acknowledged']);
    }

    public function test_resident_only_sees_own_reports_and_cannot_view_others(): void
    {
        $residentA = User::factory()->create(['role' => 'resident']);
        $residentB = User::factory()->create(['role' => 'resident']);

        $reportA = Report::create([
            'reference_number' => 'BRGY-20261022-0001',
            'user_id' => $residentA->id,
            'category_id' => $this->roadCategory->id,
            'title' => 'Resident A Road Damage',
            'description' => 'Damaged sidewalk concrete in Purok 1.',
            'priority' => 'medium',
            'status' => 'submitted',
            'latitude' => 14.1810,
            'longitude' => 121.2840,
            'address_text' => 'Purok 1',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        $reportB = Report::create([
            'reference_number' => 'BRGY-20261022-0002',
            'user_id' => $residentB->id,
            'category_id' => $this->roadCategory->id,
            'title' => 'Resident B Private Report',
            'description' => 'Broken drainage cover in Purok 5.',
            'priority' => 'low',
            'status' => 'submitted',
            'latitude' => 14.1820,
            'longitude' => 121.2850,
            'address_text' => 'Purok 5',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        Sanctum::actingAs($residentA);

        // My reports returns only Resident A's report
        $listResponse = $this->getJson('/api/v1/my-reports');
        $listResponse->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $reportA->id);

        // Viewing Resident B's report is forbidden by ReportPolicy
        $this->getJson('/api/v1/reports/'.$reportB->id)
            ->assertForbidden()
            ->assertJsonPath('message', 'You are not authorized to access this resource.');
    }

    public function test_full_end_to_end_flow_between_mobile_api_and_admin_web(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);
        $admin = User::factory()->create(['role' => 'admin']);

        Sanctum::actingAs($resident);

        // 1. Resident submits report via mobile API
        $createResponse = $this->postJson('/api/v1/reports', [
            'category_id' => $this->roadCategory->id,
            'title' => 'Collapsed canal wall in Purok 6',
            'description' => 'Canal wall collapsed and is blocking water flow during rain.',
            'priority' => 'high',
            'latitude' => 14.1825,
            'longitude' => 121.2860,
            'address_text' => 'Purok 6, Canal Road',
        ]);

        $createResponse->assertCreated();
        $reportId = $createResponse->json('data.id');
        $report = Report::findOrFail($reportId);

        // 2. Admin opens the Triage Drawer in Dashboard, assigns personnel, and updates status
        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->call('selectReport', $report->id)
            ->assertSet('selectedReportId', $report->id)
            ->set('drawerStaffId', $admin->id)
            ->set('drawerStatus', 'in_progress')
            ->set('drawerNote', 'Engineering crew dispatched with cement and rebar.')
            ->call('saveTriageUpdate')
            ->assertHasNoErrors();

        // 3. Resident fetches report detail & community pins via mobile API and sees the updated status & timeline
        $detailResponse = $this->getJson('/api/v1/reports/'.$reportId);
        $detailResponse->assertOk()
            ->assertJsonPath('data.status', 'in_progress')
            ->assertJsonPath('data.status_label', 'In Progress')
            ->assertJsonPath('data.assigned_to_name', $admin->name)
            ->assertJsonCount(2, 'data.status_histories');

        $listResponse = $this->getJson('/api/v1/my-reports');
        $listResponse->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => [
                    'stats' => ['total', 'active', 'resolved', 'safety_alerts'],
                    'community_pins',
                ],
            ]);
    }
}
