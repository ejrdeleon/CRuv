<?php

namespace Tests\Feature;

use App\Livewire\Admin\ReportDetail as AdminReportDetail;
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
            'name' => 'Municipal Engineering & Public Works',
            'code' => 'MEPW',
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

    public function test_admin_can_update_status_and_assign_department(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
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
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'P. Guevara Ave',
            'barangay' => 'Poblacion I',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
        ]);

        // Admin assigns report to department
        Livewire::actingAs($admin)
            ->test(AdminReportDetail::class, ['report' => $report])
            ->set('assignedDepartmentId', $dept->id)
            ->set('assignmentNotes', 'Deploy crew with concrete patching batch.')
            ->call('assignReport')
            ->assertHasNoErrors();

        $report->refresh();
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
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'Gated Village Lane',
            'barangay' => 'Pagsawitan',
            'city' => 'Santa Cruz',
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
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'Main Street',
            'barangay' => 'Poblacion II',
            'city' => 'Santa Cruz',
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
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'Main Street',
            'barangay' => 'Poblacion II',
            'city' => 'Santa Cruz',
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
