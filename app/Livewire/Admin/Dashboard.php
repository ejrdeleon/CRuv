<?php

namespace App\Livewire\Admin;

use App\Models\ActivityLog;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        // 1. Calculate actual database stats
        $totalReports = Report::count();
        $newReports = Report::where('status', 'submitted')->count();
        $awaitingVerification = Report::whereIn('status', ['submitted', 'under_review'])->count();
        $urgentReports = Report::whereIn('priority', ['urgent', 'high'])
            ->whereNotIn('status', ['resolved', 'rejected', 'duplicate'])
            ->count();
        $inProgressReports = Report::where('status', 'in_progress')->count();
        $resolvedReports = Report::where('status', 'resolved')->count();

        // 2. Reports by status breakdown
        $statusCounts = Report::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // 3. Reports by category breakdown
        $categoryBreakdown = ReportCategory::withCount('reports')
            ->orderByDesc('reports_count')
            ->get();

        // 4. Reports by priority
        $priorityCounts = Report::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->pluck('count', 'priority')
            ->toArray();

        // 5. Recent reports
        $recentReports = Report::with(['category', 'user', 'assignedStaff', 'department'])
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // 6. Map reports (all active and recent resolved)
        $mapReports = Report::with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderByDesc('created_at')
            ->take(60)
            ->get()
            ->map(function ($r) {
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

        // 7. Recent activity logs
        $recentActivities = ActivityLog::with('user')
            ->orderByDesc('created_at')
            ->take(6)
            ->get();

        return view('livewire.admin.dashboard', [
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
            'recentActivities' => $recentActivities,
        ]);
    }
}
