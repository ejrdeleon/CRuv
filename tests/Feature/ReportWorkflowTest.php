<?php

namespace Tests\Feature;

use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\ReportDetail as AdminReportDetail;
use App\Livewire\Admin\ReportManagement as AdminReportManagement;
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

    public function test_issue_queue_shares_filtered_data_with_map_and_handles_row_selection_and_null_coordinates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $resident = User::factory()->create(['role' => 'resident']);
        $category = ReportCategory::first();

        $mappedReport = Report::create([
            'reference_number' => 'BRGY-MAP-0001',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Flooded intersection near plaza',
            'description' => 'Deep standing water blocking tricycles.',
            'priority' => 'urgent',
            'status' => 'submitted',
            'latitude' => 14.2816,
            'longitude' => 121.4146,
            'address_text' => 'P. Guevara Ave',
            'barangay' => 'Poblacion I',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
        ]);

        $noCoordsReport = Report::create([
            'reference_number' => 'BRGY-MAP-0002',
            'user_id' => $resident->id,
            'category_id' => $category->id,
            'title' => 'Unmapped streetlight outage',
            'description' => 'Reported without GPS coordinates.',
            'priority' => 'low',
            'status' => 'resolved',
            'latitude' => 0.0,
            'longitude' => 0.0,
            'address_text' => 'Sitio Tres',
            'barangay' => 'Bubukal',
            'city' => 'Santa Cruz',
            'province' => 'Laguna',
        ]);

        // 1. Operations Workspace renders the shared spatial map with mapped reports
        Livewire::actingAs($admin)
            ->test(AdminDashboard::class)
            ->assertSee('admin-dashboard-map')
            ->assertViewHas('mapReports', function (array $markers) use ($mappedReport) {
                return count($markers) === 1 && $markers[0]['id'] === $mappedReport->id;
            });

        // 2. Issue Queue & Case Management renders the shared spatial map and syncs markers with table filters
        $component = Livewire::actingAs($admin)
            ->test(AdminReportManagement::class)
            ->assertSee('issue-queue-map')
            ->assertViewHas('mapReports', function (array $markers) use ($mappedReport) {
                return count($markers) === 1 && $markers[0]['id'] === $mappedReport->id;
            });

        // Filtering by 'resolved' excludes the 'submitted' mapped report from both table and map
        $component->set('statusFilter', 'resolved')
            ->assertViewHas('mapReports', function (array $markers) {
                return count($markers) === 0;
            })
            ->assertDispatched('sync-admin-map-markers');

        // Resetting filters restores the mapped report
        $component->call('resetFilters')
            ->assertViewHas('mapReports', function (array $markers) {
                return count($markers) === 1;
            });

        // 3. Selecting a row with valid coordinates opens the triage drawer and dispatches pan-admin-map
        $component->call('selectReport', $mappedReport->id)
            ->assertSet('selectedReportId', $mappedReport->id)
            ->assertSet('drawerOpen', true)
            ->assertSet('mapCoordWarning', null)
            ->assertDispatched('pan-admin-map', lat: 14.2816, lng: 121.4146, id: $mappedReport->id);

        // 4. Selecting a row with null coordinates opens the triage drawer and sets a clear warning without crashing
        $component->call('selectReport', $noCoordsReport->id)
            ->assertSet('selectedReportId', $noCoordsReport->id)
            ->assertSet('drawerOpen', true)
            ->assertSet('mapCoordWarning', 'Report #BRGY-MAP-0002 has no valid GPS coordinates to pin on the map.');
    }
}
