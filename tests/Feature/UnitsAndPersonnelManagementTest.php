<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\StaffManagement;
use App\Models\Department;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class UnitsAndPersonnelManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_units_and_personnel_management_registry(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $unit = Department::create([
            'name' => 'Municipal Health Office',
            'code' => 'MHO',
            'contact_person' => 'Dr. Santos',
            'contact_phone' => '0919-456-7890',
            'is_active' => true,
        ]);

        $personnel = Personnel::create([
            'department_id' => $unit->id,
            'name' => 'Clarissa Mendoza',
            'position' => 'Sanitary Inspector II',
            'contact_number' => '0919-456-7891',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->assertStatus(200)
            ->assertSee('Municipal Health Office')
            ->assertSee('MHO')
            ->assertSee('Clarissa Mendoza')
            ->assertSee('Sanitary Inspector II');
    }

    public function test_admin_can_create_unit_and_personnel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);

        Livewire::test(StaffManagement::class)
            ->set('deptName', 'Traffic Management Unit')
            ->set('deptCode', 'TMU')
            ->set('deptContactPerson', 'Rolando Salazar')
            ->set('deptContactPhone', '0916-234-5678')
            ->call('saveDepartment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('departments', [
            'code' => 'TMU',
            'name' => 'Traffic Management Unit',
        ]);

        $dept = Department::where('code', 'TMU')->first();

        Livewire::test(StaffManagement::class)
            ->set('personnelDeptId', $dept->id)
            ->set('personnelName', 'Nestor Aquino')
            ->set('personnelPosition', 'Lead Traffic Enforcer')
            ->set('personnelContactNumber', '0916-234-5679')
            ->call('savePersonnel')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('personnel', [
            'department_id' => $dept->id,
            'name' => 'Nestor Aquino',
            'position' => 'Lead Traffic Enforcer',
        ]);
    }

    public function test_admin_can_assign_report_to_unit_and_personnel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);

        $unit = Department::create([
            'name' => 'Engineering Office',
            'code' => 'MEO',
            'is_active' => true,
        ]);

        $personnel = Personnel::create([
            'department_id' => $unit->id,
            'name' => 'Dennis Alcantara',
            'position' => 'Road Inspector',
            'contact_number' => '0918-123-4568',
            'is_active' => true,
        ]);

        $cat = ReportCategory::create([
            'name' => 'Pothole',
            'slug' => 'pothole',
            'department_id' => $unit->id,
            'is_active' => true,
        ]);

        $report = Report::create([
            'reference_number' => 'BRGY-TEST-001',
            'user_id' => $resident->id,
            'category_id' => $cat->id,
            'title' => 'Deep pothole on main road',
            'description' => 'Dangerous for tricycles',
            'priority' => 'high',
            'status' => 'submitted',
            'latitude' => 14.281,
            'longitude' => 121.415,
            'address_text' => 'Poblacion I, Santa Cruz',
            'barangay' => 'Poblacion I',
        ]);

        $this->actingAs($admin);

        // Test triage drawer in dashboard
        Livewire::test(Dashboard::class)
            ->call('selectReport', $report->id)
            ->set('drawerDepartmentId', $unit->id)
            ->set('drawerPersonnelId', $personnel->id)
            ->set('drawerNote', 'Assigned for patch repair')
            ->call('saveTriageUpdate')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertEquals($unit->id, $report->department_id);
        $this->assertEquals($personnel->id, $report->personnel_id);
        $this->assertEquals('assigned', $report->status);

        $this->assertDatabaseHas('report_assignments', [
            'report_id' => $report->id,
            'department_id' => $unit->id,
            'personnel_id' => $personnel->id,
        ]);

        // Verify API exposes assigned_unit_name to resident without exposing staff contact details
        $this->actingAs($resident);
        $response = $this->getJson('/api/v1/reports/'.$report->id);
        $response->assertOk()
            ->assertJsonPath('data.assigned_unit_name', 'Engineering Office')
            ->assertJsonMissing(['contact_number' => '0918-123-4568']);
    }
}
