<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\InteractsWithTriageDrawer;
use App\Models\Report;
use App\Models\ReportCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class InteractiveMap extends Component
{
    use InteractsWithTriageDrawer;

    public string $categoryFilter = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $barangayFilter = '';

    public string $search = '';

    public function clearSelectedReport(): void
    {
        $this->closeDrawer();
    }

    public function resetFilters(): void
    {
        $this->reset(['categoryFilter', 'statusFilter', 'priorityFilter', 'barangayFilter', 'search', 'selectedReportId', 'drawerOpen']);
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

        $mapMarkers = $this->formatMapMarkers($reports);

        $this->dispatch('sync-admin-map-markers', markers: $mapMarkers);

        $categories = ReportCategory::where('is_active', true)->orderBy('name')->get();
        $selectedReport = $this->selectedReportId
            ? Report::with(['category', 'images', 'department', 'assignedStaff'])->find($this->selectedReportId)
            : null;

        return view('livewire.admin.interactive-map', [
            'reports' => $reports,
            'mapMarkers' => $mapMarkers,
            'categories' => $categories,
            'selectedReport' => $selectedReport,
        ]);
    }
}
