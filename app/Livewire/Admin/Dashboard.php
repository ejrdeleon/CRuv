<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Department;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportAssignment;
use App\Models\ReportCategory;
use App\Models\ReportStatusHistory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
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

        if ($report->latitude && $report->longitude) {
            $this->dispatch('pan-admin-map', lat: (float) $report->latitude, lng: (float) $report->longitude, id: $report->id);
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

    public function render()
    {
        // 1. Metric-driven Top Stat Row (Real Data)
        $totalActiveTickets = Report::whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress'])->count();
        $highPriorityUnassigned = Report::whereIn('priority', ['urgent', 'high'])
            ->whereNull('assigned_to')
            ->whereNotIn('status', ['resolved', 'rejected', 'duplicate'])
            ->count();

        $resolvedForAvg = Report::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->get(['created_at', 'resolved_at']);

        $avgHours = $resolvedForAvg->isNotEmpty()
            ? round($resolvedForAvg->avg(fn (Report $r) => max(0.5, $r->created_at->diffInMinutes($r->resolved_at) / 60)), 1)
            : null;

        $totalReports = Report::count();
        $newReports = Report::where('status', 'submitted')->count();
        $awaitingVerification = Report::whereIn('status', ['submitted', 'under_review'])->count();
        $urgentReports = Report::whereIn('priority', ['urgent', 'high'])
            ->whereNotIn('status', ['resolved', 'rejected', 'duplicate'])
            ->count();
        $inProgressReports = Report::where('status', 'in_progress')->count();
        $resolvedReports = Report::where('status', 'resolved')->count();

        $statusCounts = Report::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $categoryBreakdown = ReportCategory::withCount('reports')
            ->orderByDesc('reports_count')
            ->get();

        $priorityCounts = Report::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // Dense Hybrid Workspace Table Rows
        $recentReports = Report::with(['category', 'user', 'assignedStaff', 'department'])
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        // Map Pins
        $mapReports = Report::with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('created_at')
            ->take(60)
            ->get()
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
            });

        $selectedReport = $this->selectedReportId
            ? Report::with(['category', 'user', 'assignedStaff', 'department', 'images', 'statusHistories.user'])
                ->find($this->selectedReportId)
            : null;

        $departments = Department::where('is_active', true)->get();
        $staffMembers = User::whereIn('role', ['admin', 'staff'])->where('is_active', true)->get();

        return view('livewire.admin.dashboard', [
            'totalActiveTickets' => $totalActiveTickets,
            'avgResolutionHours' => $avgHours,
            'highPriorityUnassigned' => $highPriorityUnassigned,
            'totalReports' => $totalReports,
            'newReports' => $newReports,
            'awaitingVerification' => $awaitingVerification,
            'urgentReports' => $urgentReports,
            'inProgressReports' => $inProgressReports,
            'resolvedReports' => $resolvedReports,
            'statusCounts' => $statusCounts,
            'categoryBreakdown' => $categoryBreakdown,
            'priorityCounts' => $priorityCounts,
            'recentReports' => $recentReports,
            'mapReports' => $mapReports,
            'selectedReport' => $selectedReport,
            'departments' => $departments,
            'staffMembers' => $staffMembers,
        ]);
    }
}
