<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\InteractsWithTriageDrawer;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use InteractsWithTriageDrawer;

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

        // Map Pins (Shared formatter)
        $mapReports = $this->formatMapMarkers(
            Report::with('category')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->orderByDesc('created_at')
                ->take(60)
                ->get()
        );

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
