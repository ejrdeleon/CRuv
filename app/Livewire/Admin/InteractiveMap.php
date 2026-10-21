<?php

namespace App\Livewire\Admin;

use App\Models\Report;
use App\Models\ReportCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class InteractiveMap extends Component
{
    public string $categoryFilter = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $barangayFilter = '';

    public string $search = '';

    public ?int $selectedReportId = null;

    public function selectReport(int $id)
    {
        $this->selectedReportId = $id;
    }

    public function clearSelectedReport()
    {
        $this->selectedReportId = null;
    }

    public function resetFilters()
    {
        $this->reset(['categoryFilter', 'statusFilter', 'priorityFilter', 'barangayFilter', 'search', 'selectedReportId']);
    }

    public function render()
    {
        $query = Report::with(['category', 'department', 'images'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%')
                    ->orWhere('address_text', 'like', '%'.$this->search.'%');
            });
        }

        if (! empty($this->categoryFilter)) {
            $query->where('category_id', $this->categoryFilter);
        }

        if (! empty($this->statusFilter)) {
            $query->where('status', $this->statusFilter);
        }

        if (! empty($this->priorityFilter)) {
            $query->where('priority', $this->priorityFilter);
        }

        if (! empty($this->barangayFilter)) {
            $query->where('barangay', $this->barangayFilter);
        }

        $reports = $query->orderByDesc('created_at')->get();

        $mapMarkers = $reports->map(function ($r) {
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
                'barangay' => $r->barangay,
                'submitted_at' => $r->created_at->format('M d, Y'),
                'image_url' => $r->images->first()?->url ?? null,
                'url' => route('admin.reports.show', $r->id),
            ];
        });

        $categories = ReportCategory::where('is_active', true)->get();
        $selectedReport = $this->selectedReportId ? Report::with(['category', 'images', 'department', 'assignedStaff'])->find($this->selectedReportId) : null;

        return view('livewire.admin.interactive-map', [
            'reports' => $reports,
            'mapMarkers' => $mapMarkers,
            'categories' => $categories,
            'selectedReport' => $selectedReport,
        ]);
    }
}
