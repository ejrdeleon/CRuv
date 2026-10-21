<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReportDetail as AdminReportDetail;
use App\Livewire\Resident\ReportDetail as ResidentReportDetail;
use App\Livewire\Resident\SubmitReport;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Department::create([
            'name' => 'Barangay Public Works',
            'code' => 'BPWE',
            'is_active' => true,
        ]);

        ReportCategory::create([
            'name' => 'Damaged Roads & Potholes',
            'slug' => 'damaged-roads',
            'default_priority' => 'medium',
            'is_hazardous' => false,
            'is_active' => true,
        ]);
    }

    public function test_resident_can_submit_damage_report(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        Livewire::actingAs($resident)
            ->test(SubmitReport::class)
            ->set('category_id', $category->id)
            ->set('title', 'Large crater pothole on highway')
            ->set('description', 'Pothole expanded following flash flood, causing motorbikes to veer into oncoming traffic.')
            ->set('priority', 'high')
            ->set('latitude', 14.1812)
            ->set('longitude', 121.2841)
            ->set('address_text', 'National Highway near plaza')
            ->set('barangay', 'Barangay San Isidro')
            ->set('city', 'Bay')
            ->set('province', 'Laguna')
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('reports', [
            'user_id' => $resident->id,
            'title' => 'Large crater pothole on highway',
            'status' => 'submitted',
            'priority' => 'high',
        ]);

        $report = Report::where('user_id', $resident->id)->first();
        $this->assertNotNull($report);
        $this->assertStringStartsWith('BRGY-', $report->reference_number);

        // Verification notification sent
        $this->assertDatabaseHas('notifications', [
            'user_id' => $resident->id,
            'report_id' => $report->id,
        ]);
    }

    public function test_resident_cannot_view_another_residents_private_report(): void
    {
        $resident1 = User::factory()->create(['role' => 'resident']);
        $resident2 = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        $report = Report::create([
            'reference_number' => 'BRGY-TEST-0001',
            'user_id' => $resident1->id,
            'category_id' => $category->id,
            'title' => 'Resident 1 Private Issue',
            'description' => 'Detailed problem description for resident 1 only.',
            'priority' => 'medium',
            'status' => 'submitted',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Purok 1',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        // Resident 2 attempts to view resident 1's report
        $response = $this->actingAs($resident2)->get(route('resident.reports.show', $report->id));
        $response->assertStatus(403);
    }

    public function test_admin_can_update_status_and_assign_staff(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();
        $dept = Department::first();

        $report = Report::create([
            'reference_number' => 'BRGY-TEST-0002',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Broken road culvert',
            'description' => 'Culvert broken by heavy truck delivery.',
            'priority' => 'high',
            'status' => 'submitted',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Purok 2',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        // Admin assigns report to staff
        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $report])
            ->set('assignedDepartmentId', $dept->id)
            ->set('assignedStaffId', $staff->id)
            ->set('assignmentNotes', 'Deploy crew with concrete patching batch.')
            ->call('assignReport')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertEquals($staff->id, $report->assigned_to);
        $this->assertEquals($dept->id, $report->department_id);

        // Admin moves status to in_progress
        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $report])
            ->set('newStatus', 'in_progress')
            ->set('statusComment', 'Field inspection underway')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertEquals('in_progress', $report->status);

        // Check status history logged
        $this->assertDatabaseHas('report_status_histories', [
            'report_id' => $report->id,
            'to_status' => 'in_progress',
        ]);
    }

    public function test_resident_can_only_edit_report_while_submitted(): void
    {
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        $report = Report::create([
            'reference_number' => 'BRGY-TEST-0003',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Original Title Here',
            'description' => 'Original report description with enough length.',
            'priority' => 'low',
            'status' => 'submitted',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Purok 1',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        // Resident CAN edit while submitted
        Livewire::actingAs($resident)
            ->test(ResidentReportDetail::class, ['report' => $report])
            ->call('startEdit')
            ->set('editTitle', 'Updated Title By Resident')
            ->set('editDescription', 'Updated description by resident with sufficient length.')
            ->call('saveEdit')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertEquals('Updated Title By Resident', $report->title);

        // Now admin verifies report
        $report->update(['status' => 'verified']);

        // Resident CANNOT edit now (isEditing remains false)
        Livewire::actingAs($resident)
            ->test(ResidentReportDetail::class, ['report' => $report])
            ->call('startEdit')
            ->assertSet('isEditing', false);
    }

    public function test_admin_can_reject_report_with_mandatory_reason(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        $report = Report::create([
            'reference_number' => 'BRGY-TEST-0004',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Out of jurisdiction complaint',
            'description' => 'Complaint on private road inside gated subdivision with private HOA maintenance.',
            'priority' => 'low',
            'status' => 'submitted',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Gated Village Lane',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        // Attempt rejection without reason -> validation error
        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $report])
            ->set('newStatus', 'rejected')
            ->set('rejectionReason', '')
            ->call('updateStatus')
            ->assertHasErrors(['rejectionReason']);

        // Rejection with reason -> success
        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $report])
            ->set('newStatus', 'rejected')
            ->set('rejectionReason', 'Private road jurisdiction under HOA maintenance.')
            ->call('updateStatus')
            ->assertHasNoErrors();

        $report->refresh();
        $this->assertEquals('rejected', $report->status);
        $this->assertEquals('Private road jurisdiction under HOA maintenance.', $report->rejection_reason);
    }

    public function test_admin_can_mark_report_as_duplicate(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        $primaryReport = Report::create([
            'reference_number' => 'BRGY-PRIMARY-0001',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Original pothole report',
            'description' => 'Original pothole reported on Main Street.',
            'priority' => 'medium',
            'status' => 'verified',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Main Street',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        $secondReport = Report::create([
            'reference_number' => 'BRGY-DUP-0002',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Duplicate pothole notice',
            'description' => 'Also reporting the same pothole on Main Street.',
            'priority' => 'medium',
            'status' => 'submitted',
            'latitude' => 14.18,
            'longitude' => 121.28,
            'address_text' => 'Main Street',
            'barangay' => 'Barangay San Isidro',
            'city' => 'Bay',
            'province' => 'Laguna',
        ]);

        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $secondReport])
            ->set('newStatus', 'duplicate')
            ->set('duplicateOfId', $primaryReport->id)
            ->call('updateStatus')
            ->assertHasNoErrors();

        $secondReport->refresh();
        $this->assertEquals('duplicate', $secondReport->status);
        $this->assertEquals($primaryReport->id, $secondReport->duplicate_of_id);
    }
}
