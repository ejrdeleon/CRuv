<?php

namespace App\Livewire\Admin;

use App\Models\Department;
use App\Models\Report;
use App\Models\ReportCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ReportManagement extends Component
{
    use WithPagination;

    public string $search = '';

    public string $categoryFilter = '';

    public string $statusFilter = '';

    public string $priorityFilter = '';

    public string $departmentFilter = '';

    public string $viewMode = 'table'; // 'table' or 'map'

    public string $sortField = 'created_at';

    public string $sortDirection = 'desc';

    public function sortBy(string $field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedPriorityFilter()
    {
        $this->resetPage();
    }

    public function updatedDepartmentFilter()
    {
        $this->resetPage();
    }

    public function resetFilters()
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

        $reports = $query->paginate(12);

        // For map view:
        $mapReports = (clone $query)->whereNotNull('latitude')->whereNotNull('longitude')->get()->map(function ($r) {
            return [
                'id' => $r->id,
                'ref' => $r->reference_number,
                'title' => $r->title,
                'category' => $r->category->name ?? '',
                'status' => $r->status,
                'status_label' => $r->status_label,
                'priority' => $r->priority,
                'lat' => (float) $r->latitude,
                'lng' => (float) $r->longitude,
                'url' => route('admin.reports.show', $r->id),
            ];
        });

        $categories = ReportCategory::where('is_active', true)->get();
        $departments = Department::where('is_active', true)->get();

        return view('livewire.admin.report-management', [
            'reports' => $reports,
            'mapReports' => $mapReports,
            'categories' => $categories,
            'departments' => $departments,
        ]);
    }
}
