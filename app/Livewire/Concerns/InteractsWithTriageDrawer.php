<?php

namespace App\Livewire\Concerns;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportStatusHistory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait InteractsWithTriageDrawer
{
    public ?int $selectedReportId = null;

    public bool $drawerOpen = false;

    public string $drawerStatus = '';

    public ?int $drawerDepartmentId = null;

    public ?int $drawerPersonnelId = null;

    public ?int $drawerStaffId = null;

    public string $drawerNote = '';

    public ?string $mapCoordWarning = null;

    public function selectReport(int $reportId): void
    {
        $report = Report::with(['category', 'department', 'personnel'])->find($reportId);
        if (! $report) {
            return;
        }

        $this->selectedReportId = $report->id;
        $this->drawerOpen = true;
        $this->drawerStatus = $report->status;
        // Suggest likely unit from report category default if not yet assigned
        $this->drawerDepartmentId = $report->department_id ?: $report->category?->department_id;
        $this->drawerPersonnelId = $report->personnel_id;
        $this->drawerStaffId = $report->assigned_to;
        $this->drawerNote = $report->resolution_notes ?? '';

        if ($this->hasValidCoordinates($report)) {
            $this->mapCoordWarning = null;
            $this->dispatch(
                'pan-admin-map',
                lat: (float) $report->latitude,
                lng: (float) $report->longitude,
                id: $report->id
            );
        } else {
            $this->mapCoordWarning = "Report #{$report->reference_number} has no valid GPS coordinates to pin on the map.";
        }
    }

    public function updatedDrawerDepartmentId(): void
    {
        if ($this->drawerPersonnelId) {
            $belongs = Personnel::where('id', $this->drawerPersonnelId)
                ->where('department_id', $this->drawerDepartmentId)
                ->where('is_active', true)
                ->exists();

            if (! $belongs) {
                $this->drawerPersonnelId = null;
            }
        }
    }

    public function closeDrawer(): void
    {
        $this->drawerOpen = false;
        $this->selectedReportId = null;
        $this->mapCoordWarning = null;
    }

    public function hasValidCoordinates(Report $report): bool
    {
        if ($report->latitude === null || $report->longitude === null) {
            return false;
        }
        if (! is_numeric($report->latitude) || ! is_numeric($report->longitude)) {
            return false;
        }
        $lat = (float) $report->latitude;
        $lng = (float) $report->longitude;

        return $lat >= -90.0 && $lat <= 90.0
            && $lng >= -180.0 && $lng <= 180.0
            && (abs($lat) > 0.001 || abs($lng) > 0.001);
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
        $report->personnel_id = $this->drawerPersonnelId ?: null;
        if ($this->drawerStaffId) {
            $report->assigned_to = $this->drawerStaffId;
        }

        if (($this->drawerDepartmentId || $this->drawerPersonnelId || $this->drawerStaffId) && in_array($newStatus, ['submitted', 'under_review', 'verified'], true)) {
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

        if ($this->drawerDepartmentId || $this->drawerPersonnelId || $this->drawerStaffId) {
            $dept = $this->drawerDepartmentId ? Department::find($this->drawerDepartmentId) : null;
            $person = $this->drawerPersonnelId ? Personnel::find($this->drawerPersonnelId) : null;
            $assigneeText = $dept ? $dept->name : '';
            if ($person) {
                $assigneeText = $assigneeText ? "{$assigneeText}, {$person->name}" : $person->name;
            }

            ReportAssignment::create([
                'report_id' => $report->id,
                'department_id' => $this->drawerDepartmentId,
                'personnel_id' => $this->drawerPersonnelId,
                'assigned_by_user_id' => Auth::id(),
                'assigned_to_user_id' => $this->drawerStaffId,
                'assignment_notes' => trim($this->drawerNote) ?: "Assigned to {$assigneeText}",
                'assigned_at' => now(),
            ]);

            ReportStatusHistory::create([
                'report_id' => $report->id,
                'user_id' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => "Assigned to {$assigneeText}".(trim($this->drawerNote) ? ' — '.trim($this->drawerNote) : ''),
            ]);
        } elseif ($oldStatus !== $newStatus || trim($this->drawerNote) !== '') {
            ReportStatusHistory::create([
                'report_id' => $report->id,
                'user_id' => Auth::id(),
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'comment' => trim($this->drawerNote) ?: "Status updated to {$report->status_label}",
            ]);
        }

        Notification::create([
            'user_id' => $report->user_id,
            'report_id' => $report->id,
            'title' => "Report Status Updated: {$report->status_label}",
            'message' => "Your report #{$report->reference_number} is now {$report->status_label}.",
            'type' => 'status_updated',
            'is_read' => false,
        ]);

        ActivityLog::log(
            'triage_updated',
            "Report #{$report->reference_number} triaged to {$newStatus}",
            $report
        );

        session()->flash('success', "Ticket #{$report->reference_number} updated ({$report->status_label}).");

        // Invalidate cached aggregates so dashboards and maps refresh instantly
        Cache::forget('admin:dashboard:metrics');
        Cache::forget('api:community_pins');
        Cache::forget('api:safety_alerts_count');
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
            ->filter(fn (Report $r) => $this->hasValidCoordinates($r))
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
