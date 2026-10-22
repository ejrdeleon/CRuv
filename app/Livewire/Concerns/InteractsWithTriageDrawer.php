<?php

namespace App\Livewire\Concerns;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportStatusHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

trait InteractsWithTriageDrawer
{
    public ?int $selectedReportId = null;

    public string $drawerStatus = '';

    public ?int $drawerDepartmentId = null;

    public ?int $drawerStaffId = null;

    public string $drawerNote = '';

    public function selectReport(int $reportId): void
    {
        $report = Report::find($reportId);
        if (! $report) {
            return;
        }

        $this->selectedReportId = $report->id;
        $this->drawerStatus = $report->status;
        $this->drawerDepartmentId = $report->department_id;
        $this->drawerStaffId = $report->assigned_to;
        $this->drawerNote = $report->resolution_notes ?? '';

        if ($report->latitude !== null && $report->longitude !== null && is_numeric($report->latitude) && is_numeric($report->longitude)) {
            $this->dispatch(
                'pan-admin-map',
                lat: (float) $report->latitude,
                lng: (float) $report->longitude,
                id: $report->id
            );
        }
    }

    public function closeDrawer(): void
    {
        $this->selectedReportId = null;
    }

    public function saveTriageUpdate(): void
    {
        if (! $this->selectedReportId) {
            return;
        }

        $report = Report::find($this->selectedReportId);
        if (! $report) {
            return;
        }

        $oldStatus = $report->status;
        $newStatus = $this->drawerStatus ?: $oldStatus;

        $report->department_id = $this->drawerDepartmentId ?: null;
        $report->assigned_to = $this->drawerStaffId ?: null;

        if ($this->drawerStaffId && in_array($newStatus, ['submitted', 'under_review', 'verified'], true)) {
            $newStatus = 'assigned';
            $this->drawerStatus = 'assigned';
        }

        if ($newStatus === 'verified' && ! $report->verified_at) {
            $report->verified_at = now();
        }
        if ($newStatus === 'assigned' && ! $report->assigned_at) {
            $report->assigned_at = now();
        }
        if ($newStatus === 'in_progress' && ! $report->in_progress_at) {
            $report->in_progress_at = now();
        }
        if ($newStatus === 'resolved') {
            $report->resolved_at = $report->resolved_at ?? now();
            if (trim($this->drawerNote) !== '') {
                $report->resolution_notes = trim($this->drawerNote);
            }
        }

        $report->status = $newStatus;
        $report->save();

        if ($this->drawerDepartmentId || $this->drawerStaffId) {
            ReportAssignment::create([
                'report_id' => $report->id,
                'department_id' => $this->drawerDepartmentId,
                'assigned_by_user_id' => Auth::id(),
                'assigned_to_user_id' => $this->drawerStaffId,
                'assignment_notes' => trim($this->drawerNote) ?: 'Updated via Triage Drawer',
                'assigned_at' => now(),
            ]);
        }

        if ($oldStatus !== $newStatus || trim($this->drawerNote) !== '') {
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'user_id' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => trim($this->drawerNote) ?: "Status updated to {$report->status_label}",
            ]);

            Notification::create([
                'user_id' => $report->user_id,
                'report_id' => $report->id,
                'title' => "Report Status Updated: {$report->status_label}",
                'message' => "Your report #{$report->reference_number} is now {$report->status_label}.",
                'type' => 'status_updated',
                'is_read' => false,
            ]);
        }

        ActivityLog::log(
            'triage_updated',
            "Report #{$report->reference_number} triaged to {$newStatus}",
            $report
        );

        session()->flash('success', "Ticket #{$report->reference_number} updated ({$report->status_label}).");
    }

    /**
     * Formats a collection of reports with valid coordinates into map markers.
     *
     * @param  Collection<int, Report>  $reports
     * @return array<int, array<string, mixed>>
     */
    protected function formatMapMarkers($reports): array
    {
        return $reports
            ->filter(fn (Report $r) => $r->latitude !== null && $r->longitude !== null && is_numeric($r->latitude) && is_numeric($r->longitude))
            ->map(function (Report $r) {
                return [
                    'id' => $r->id,
                    'ref' => $r->reference_number,
                    'title' => $r->title,
                    'category' => $r->category->name ?? 'Hazard',
                    'status' => $r->status,
                    'status_label' => $r->status_label,
                    'priority' => $r->priority,
                    'lat' => (float) $r->latitude,
                    'lng' => (float) $r->longitude,
                    'address' => $r->address_text,
                    'url' => route('admin.reports.show', $r->id),
                ];
            })
            ->values()
            ->all();
    }
}
