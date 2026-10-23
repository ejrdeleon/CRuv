<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportComment;
use App\Models\ReportStatusHistory;
use App\Services\ReportPhotoUploader;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class ReportDetail extends Component
{
    use WithFileUploads;

    public Report $report;

    // Status transition properties
    public string $newStatus = '';

    public string $statusComment = '';

    public string $rejectionReason = '';

    public ?int $duplicateOfId = null;

    // Resolution details
    public string $resolutionNotes = '';

    public $resolutionPhoto = null;

    // Assignment properties
    public ?int $assignedDepartmentId = null;

    public ?int $assignedPersonnelId = null;

    public string $assignmentNotes = '';

    // Priority adjustment
    public string $selectedPriority = '';

    // Comments
    public string $newComment = '';

    public bool $isInternalComment = false;

    // Modal state
    public bool $showStatusModal = false;

    public bool $showAssignModal = false;

    public function mount(Report $report)
    {
        $this->report = $report;
        $this->loadReportRelations();

        $this->newStatus = $report->status;
        $this->selectedPriority = $report->priority;
        $this->assignedDepartmentId = $report->department_id ?: $report->category?->department_id;
        $this->assignedPersonnelId = $report->personnel_id;
        $this->resolutionNotes = $report->resolution_notes ?? '';
    }

    private function loadReportRelations(): void
    {
        $this->report->load([
            'category:id,name,slug,description,is_hazardous,safety_advisory,color',
            'department:id,name,code',
            'personnel:id,name,position,contact_number,email,department_id',
            'assignedStaff:id,name,email',
            'duplicateOf:id,reference_number,title',
            'duplicates:id,reference_number,title,duplicate_of_id',
            'user:id,name,phone,email,barangay',
            'images',
            'statusHistories.user:id,name',
            'comments.user:id,name,role',
        ]);
    }

    public function updatePriority()
    {
        $oldPriority = $this->report->priority;
        $this->report->update([
            'priority' => $this->selectedPriority,
        ]);

        ActivityLog::log(
            'priority_updated',
            "Report #{$this->report->reference_number} priority changed from {$oldPriority} to {$this->selectedPriority}",
            $this->report,
            ['old' => $oldPriority, 'new' => $this->selectedPriority]
        );

        session()->flash('success', 'Priority updated to '.strtoupper($this->selectedPriority));
    }

    public function openStatusModal()
    {
        $this->statusComment = '';
        $this->rejectionReason = '';
        $this->duplicateOfId = null;
        $this->showStatusModal = true;
    }

    public function updateStatus()
    {
        $fromStatus = $this->report->status;
        $toStatus = $this->newStatus;

        if ($toStatus === $fromStatus) {
            $this->showStatusModal = false;

            return;
        }

        // Validate transitions & requirements
        if ($toStatus === 'rejected') {
            $this->validate([
                'rejectionReason' => 'required|string|min:5|max:1000',
            ], [
                'rejectionReason.required' => 'A clear rejection reason is required when rejecting a report.',
            ]);
            $this->report->rejection_reason = $this->rejectionReason;
        } elseif ($toStatus === 'duplicate') {
            $this->validate([
                'duplicateOfId' => 'required|exists:reports,id|different:report.id',
            ], [
                'duplicateOfId.required' => 'Please select the parent report this is a duplicate of.',
            ]);
            $this->report->duplicate_of_id = $this->duplicateOfId;
        } elseif ($toStatus === 'resolved') {
            $this->validate([
                'resolutionNotes' => 'required|string|min:5|max:2000',
                'resolutionPhoto' => 'nullable|image|max:10240',
            ], [
                'resolutionNotes.required' => 'Please provide resolution notes detailing what work was performed.',
            ]);

            $this->report->resolution_notes = $this->resolutionNotes;
            $this->report->resolved_at = now();

            if ($this->resolutionPhoto) {
                $image = app(ReportPhotoUploader::class)->storeAndAttach(
                    $this->report,
                    $this->resolutionPhoto,
                    true,
                    'Post-resolution completion proof'
                );
                $this->report->resolution_image_path = $image->file_path;
            }
        }

        // Set timestamps
        if ($toStatus === 'verified' && ! $this->report->verified_at) {
            $this->report->verified_at = now();
        }
        if ($toStatus === 'assigned' && ! $this->report->assigned_at) {
            $this->report->assigned_at = now();
        }
        if ($toStatus === 'in_progress' && ! $this->report->in_progress_at) {
            $this->report->in_progress_at = now();
        }

        $this->report->status = $toStatus;
        $this->report->save();

        // Record status history
        ReportStatusHistory::create([
            'report_id' => $this->report->id,
            'user_id' => Auth::id(),
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'comment' => $this->statusComment ?: ($toStatus === 'rejected' ? $this->rejectionReason : "Status changed to {$toStatus}"),
        ]);

        // Send Notification to Reporter
        $statusLabels = [
            'under_review' => 'Under Review',
            'verified' => 'Verified by Barangay Admin',
            'assigned' => 'Assigned to Response Crew',
            'in_progress' => 'Work In Progress',
            'resolved' => 'Resolved & Work Completed',
            'rejected' => 'Rejected',
            'duplicate' => 'Marked as Duplicate',
        ];

        $label = $statusLabels[$toStatus] ?? ucfirst($toStatus);
        Notification::create([
            'user_id' => $this->report->user_id,
            'report_id' => $this->report->id,
            'title' => "Report Status Updated: {$label}",
            'message' => "Your report #{$this->report->reference_number} is now {$label}.".($this->statusComment ? " Note: {$this->statusComment}" : ''),
            'type' => 'status_updated',
            'is_read' => false,
        ]);

        ActivityLog::log(
            'status_changed',
            "Report #{$this->report->reference_number} status changed from {$fromStatus} to {$toStatus} by ".Auth::user()->name,
            $this->report,
            ['from' => $fromStatus, 'to' => $toStatus]
        );

        $this->showStatusModal = false;
        $this->report->refresh();
        $this->loadReportRelations();
        session()->flash('success', "Report status updated to {$label}.");
    }

    public function openAssignModal()
    {
        $this->assignedDepartmentId = $this->report->department_id ?: $this->report->category?->department_id;
        $this->assignedPersonnelId = $this->report->personnel_id;
        $this->assignmentNotes = '';
        $this->showAssignModal = true;
    }

    public function updatedAssignedDepartmentId(): void
    {
        if ($this->assignedPersonnelId) {
            $valid = Personnel::where('id', $this->assignedPersonnelId)
                ->where('department_id', $this->assignedDepartmentId)
                ->where('is_active', true)
                ->exists();

            if (! $valid) {
                $this->assignedPersonnelId = null;
            }
        }
    }

    public function assignReport()
    {
        $this->validate([
            'assignedDepartmentId' => 'required|exists:departments,id',
            'assignedPersonnelId' => 'nullable|exists:personnel,id',
            'assignmentNotes' => 'nullable|string|max:1000',
        ]);

        $dept = Department::findOrFail($this->assignedDepartmentId);
        $person = $this->assignedPersonnelId ? Personnel::find($this->assignedPersonnelId) : null;
        $assigneeText = $dept->name;
        if ($person) {
            $assigneeText .= ", {$person->name}";
        }

        $newStatus = in_array($this->report->status, ['submitted', 'under_review', 'verified'], true) ? 'assigned' : $this->report->status;

        $this->report->update([
            'department_id' => $this->assignedDepartmentId,
            'personnel_id' => $this->assignedPersonnelId,
            'assigned_at' => now(),
            'status' => $newStatus,
        ]);

        ReportAssignment::create([
            'report_id' => $this->report->id,
            'department_id' => $this->assignedDepartmentId,
            'personnel_id' => $this->assignedPersonnelId,
            'assigned_by_user_id' => Auth::id(),
            'assignment_notes' => $this->assignmentNotes ?: "Assigned to {$assigneeText}",
            'assigned_at' => now(),
        ]);

        ReportStatusHistory::create([
            'report_id' => $this->report->id,
            'user_id' => Auth::id(),
            'from_status' => $this->report->status,
            'to_status' => $newStatus,
            'comment' => "Assigned to {$assigneeText}".($this->assignmentNotes ? " — Notes: {$this->assignmentNotes}" : ''),
        ]);

        // Notify resident
        Notification::create([
            'user_id' => $this->report->user_id,
            'report_id' => $this->report->id,
            'title' => 'Report Assigned to Response Team',
            'message' => "Your report #{$this->report->reference_number} has been assigned to {$dept->name}.",
            'type' => 'report_assigned',
            'is_read' => false,
        ]);

        ActivityLog::log(
            'report_assigned',
            "Report #{$this->report->reference_number} assigned to {$assigneeText}",
            $this->report
        );

        $this->showAssignModal = false;
        $this->report->refresh();
        $this->loadReportRelations();
        session()->flash('success', 'Assignment updated successfully.');
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|min:3|max:2000',
        ]);

        ReportComment::create([
            'report_id' => $this->report->id,
            'user_id' => Auth::id(),
            'comment' => $this->newComment,
            'is_internal' => $this->isInternalComment,
        ]);

        // If public update, notify resident!
        if (! $this->isInternalComment) {
            Notification::create([
                'user_id' => $this->report->user_id,
                'report_id' => $this->report->id,
                'title' => 'New Update on Your Report',
                'message' => "Official update on #{$this->report->reference_number}: ".substr($this->newComment, 0, 80).'...',
                'type' => 'comment_added',
                'is_read' => false,
            ]);
        }

        ActivityLog::log(
            $this->isInternalComment ? 'internal_note_added' : 'public_update_added',
            "Comment added on #{$this->report->reference_number} (".($this->isInternalComment ? 'Internal Note' : 'Public Update').')',
            $this->report
        );

        $this->newComment = '';
        $this->report->refresh();
        $this->loadReportRelations();
        session()->flash('success', $this->isInternalComment ? 'Internal staff note added.' : 'Public update posted to resident timeline.');
    }

    public function render()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $personnel = $this->assignedDepartmentId
            ? Personnel::where('department_id', $this->assignedDepartmentId)->where('is_active', true)->orderBy('name')->get()
            : collect();
        $otherReports = Report::where('id', '!=', $this->report->id)
            ->select(['id', 'reference_number', 'title'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        return view('livewire.admin.report-detail', [
            'departments' => $departments,
            'personnel' => $personnel,
            'otherReports' => $otherReports,
        ]);
    }
}
