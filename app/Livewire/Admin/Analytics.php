<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Symfony\Component\HttpFoundation\StreamedResponse;

#[Layout('layouts.admin')]
class Analytics extends Component
{
    public string $fromDate = '';

    public string $toDate = '';

    public string $categoryFilter = '';

    public string $departmentFilter = '';

    public function mount()
    {
        $this->fromDate = now()->subMonths(3)->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function exportCsv(): StreamedResponse
    {
        $query = Report::with(['category', 'department', 'assignedStaff', 'user'])
            ->whereDate('created_at', '>=', $this->fromDate)
            ->whereDate('created_at', '<=', $this->toDate);

        if (! empty($this->categoryFilter)) {
            $query->where('category_id', $this->categoryFilter);
        }

        if (! empty($this->departmentFilter)) {
            $query->where('department_id', $this->departmentFilter);
        }

        $query->orderBy('created_at', 'desc');

        $fileName = 'barangay_damage_reports_'.date('Ymd_His').'.csv';

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($query) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Reference Number',
                'Title',
                'Category',
                'Priority',
                'Status',
                'Barangay',
                'Address',
                'Latitude',
                'Longitude',
                'Department',
                'Assigned Officer',
                'Submitted Date',
                'Resolved Date',
                'Resolution Notes',
            ]);

            foreach ($query->cursor() as $r) {
                fputcsv($file, [
                    $r->reference_number,
                    $r->title,
                    $r->category->name ?? '',
                    strtoupper($r->priority),
                    $r->status_label,
                    $r->barangay,
                    $r->address_text,
                    $r->latitude,
                    $r->longitude,
                    $r->department?->name ?? 'Unassigned',
                    $r->assignedStaff?->name ?? 'None',
                    $r->created_at->format('Y-m-d H:i:s'),
                    $r->resolved_at ? $r->resolved_at->format('Y-m-d H:i:s') : 'N/A',
                    $r->resolution_notes ?? '',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }

    public function render()
    {
        $baseQuery = Report::whereDate('created_at', '>=', $this->fromDate)
            ->whereDate('created_at', '<=', $this->toDate);

        if (! empty($this->categoryFilter)) {
            $baseQuery->where('category_id', $this->categoryFilter);
        }
        if (! empty($this->departmentFilter)) {
            $baseQuery->where('department_id', $this->departmentFilter);
        }

        $agg = (clone $baseQuery)->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved_count,
            SUM(CASE WHEN priority IN ('urgent', 'high') THEN 1 ELSE 0 END) as urgent_count
        ")->first();

        $totalCount = (int) ($agg->total ?? 0);
        $resolvedCount = (int) ($agg->resolved_count ?? 0);
        $urgentCount = (int) ($agg->urgent_count ?? 0);
        $resolutionRate = $totalCount > 0 ? round(($resolvedCount / $totalCount) * 100, 1) : 0;

        // Breakdown by barangay
        $barangayCounts = (clone $baseQuery)
            ->select('barangay', DB::raw('count(*) as count'))
            ->groupBy('barangay')
            ->pluck('count', 'barangay')
            ->toArray();

        // Breakdown by category
        $categoryBreakdown = ReportCategory::withCount(['reports' => function ($q) {
            $q->whereDate('created_at', '>=', $this->fromDate)
                ->whereDate('created_at', '<=', $this->toDate);
        }])->get();

        // Reports sample list for print
        $reportsList = (clone $baseQuery)
            ->with(['category:id,name', 'department:id,name', 'assignedStaff:id,name'])
            ->select(['id', 'reference_number', 'title', 'category_id', 'department_id', 'assigned_to', 'priority', 'status', 'barangay', 'created_at', 'resolved_at'])
            ->orderByDesc('created_at')
            ->take(30)
            ->get();

        $categories = ReportCategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('livewire.admin.analytics', [
            'totalCount' => $totalCount,
            'resolvedCount' => $resolvedCount,
            'urgentCount' => $urgentCount,
            'resolutionRate' => $resolutionRate,
            'barangayCounts' => $barangayCounts,
            'categoryBreakdown' => $categoryBreakdown,
            'reportsList' => $reportsList,
            'categories' => $categories,
            'departments' => $departments,
        ]);
    }
}
