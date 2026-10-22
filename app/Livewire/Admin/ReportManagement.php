<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\InteractsWithTriageDrawer;
use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ReportManagement extends Component
{
    use InteractsWithTriageDrawer;
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $departmentFilter = '';

    public string $viewMode = 'split'; // 'split', 'table', or 'map'

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryFilter', 'statusFilter', 'priorityFilter', 'departmentFilter']);
        $this->resetPage();
    }

    public function render()
    {
        $query = Report::with(['category', 'user', 'department', 'assignedStaff']);

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%')
                    ->orWhere('address_text', 'like', '%'.$this->search.'%')
                    ->orWhere('barangay', 'like', '%'.$this->search.'%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%'.$this->search.'%');
                    });
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

        if (! empty($this->departmentFilter)) {
            $query->where('department_id', $this->departmentFilter);
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        // Shared data source: map markers reflect the exact active table filters
        $mapReports = $this->formatMapMarkers(
            (clone $query)
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->get()
        );

        $this->dispatch('sync-admin-map-markers', markers: $mapReports);

        $reports = $query->paginate(12);

        $selectedReport = $this->selectedReportId
            ? Report::with(['category', 'user', 'assignedStaff', 'department', 'images', 'statusHistories.user'])
                ->find($this->selectedReportId)
            : null;

        $categories = ReportCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        $staffMembers = User::whereIn('role', ['admin', 'staff'])->where('is_active', true)->orderBy('name')->get();

        return view('livewire.admin.report-management', [
            'reports' => $reports,
            'mapReports' => $mapReports,
            'selectedReport' => $selectedReport,
            'categories' => $categories,
            'departments' => $departments,
            'staffMembers' => $staffMembers,
        ]);
    }
}
