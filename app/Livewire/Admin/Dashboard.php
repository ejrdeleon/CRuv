<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\InteractsWithTriageDrawer;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    use InteractsWithTriageDrawer;

    public function render()
    {
        // 1. Metric-driven Top Stat Aggregates cached for 60s (reduces 12 queries to 1-2 cached lookups)
        $metrics = Cache::remember('admin:dashboard:metrics', 60, function () {
            $agg = DB::table('reports')->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('submitted', 'under_review', 'verified', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) as submitted_count,
                SUM(CASE WHEN status IN ('submitted', 'under_review') THEN 1 ELSE 0 END) as awaiting_verification,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress_count,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
                SUM(CASE WHEN priority IN ('urgent', 'high') AND status NOT IN ('resolved', 'rejected', 'duplicate') THEN 1 ELSE 0 END) as urgent_count,
                SUM(CASE WHEN priority IN ('urgent', 'high') AND assigned_to IS NULL AND status NOT IN ('resolved', 'rejected', 'duplicate') THEN 1 ELSE 0 END) as unassigned_high_prio
            ")->first();

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

            $resolvedForAvg = DB::table('reports')
                ->where('status', 'resolved')
                ->whereNotNull('resolved_at')
                ->select(['created_at', 'resolved_at'])
                ->limit(100)
                ->get();

            $avgHours = $resolvedForAvg->isNotEmpty()
                ? round($resolvedForAvg->avg(fn ($r) => max(0.5, (strtotime($r->resolved_at) - strtotime($r->created_at)) / 3600)), 1)
                : null;

            return [
                'totalReports' => (int) ($agg->total ?? 0),
                'totalActiveTickets' => (int) ($agg->active ?? 0),
                'newReports' => (int) ($agg->submitted_count ?? 0),
                'awaitingVerification' => (int) ($agg->awaiting_verification ?? 0),
                'inProgressReports' => (int) ($agg->in_progress_count ?? 0),
                'resolvedReports' => (int) ($agg->resolved_count ?? 0),
                'urgentReports' => (int) ($agg->urgent_count ?? 0),
                'highPriorityUnassigned' => (int) ($agg->unassigned_high_prio ?? 0),
                'avgHours' => $avgHours,
                'statusCounts' => $statusCounts,
                'categoryBreakdown' => $categoryBreakdown,
                'priorityCounts' => $priorityCounts,
            ];
        });

        // 2. Dense Table Rows with strictly selected columns
        $recentReports = Report::with([
            'category:id,name',
            'user:id,name',
            'assignedStaff:id,name',
            'department:id,name',
        ])
            ->select(['id', 'reference_number', 'user_id', 'category_id', 'department_id', 'assigned_to', 'title', 'priority', 'status', 'address_text', 'created_at'])
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        // 3. Map Pins with selected coordinates only
        $mapReports = $this->formatMapMarkers(
            Report::with('category:id,name,is_hazardous')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->select(['id', 'reference_number', 'title', 'category_id', 'status', 'priority', 'latitude', 'longitude', 'address_text'])
                ->orderByDesc('created_at')
                ->take(40)
                ->get()
        );

        $selectedReport = $this->selectedReportId
            ? Report::with(['category', 'user', 'assignedStaff', 'department', 'images', 'statusHistories.user'])
                ->find($this->selectedReportId)
            : null;

        $departments = Department::where('is_active', true)->get();
        $staffMembers = User::whereIn('role', ['admin', 'staff'])->where('is_active', true)->get();

        return view('livewire.admin.dashboard', [
            'totalActiveTickets' => $metrics['totalActiveTickets'],
            'avgResolutionHours' => $metrics['avgHours'],
            'highPriorityUnassigned' => $metrics['highPriorityUnassigned'],
            'totalReports' => $metrics['totalReports'],
            'newReports' => $metrics['newReports'],
            'awaitingVerification' => $metrics['awaitingVerification'],
            'urgentReports' => $metrics['urgentReports'],
            'inProgressReports' => $metrics['inProgressReports'],
            'resolvedReports' => $metrics['resolvedReports'],
            'statusCounts' => $metrics['statusCounts'],
            'categoryBreakdown' => $metrics['categoryBreakdown'],
            'priorityCounts' => $metrics['priorityCounts'],
            'recentReports' => $recentReports,
            'mapReports' => $mapReports,
            'selectedReport' => $selectedReport,
            'departments' => $departments,
            'staffMembers' => $staffMembers,
        ]);
    }
}
