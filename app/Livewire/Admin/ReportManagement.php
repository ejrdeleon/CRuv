<?php

namespace App\Livewire\Admin;

use App\Livewire\Concerns\InteractsWithTriageDrawer;
use App\Models\Department;
use App\Models\Personnel;
use App\Models\Report;
use App\Models\ReportCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class ReportManagement extends Component
{
    use InteractsWithTriageDrawer;
    use WithPagination;

    #[Url(as: 'q', history: true)]
    public string $search = '';

    #[Url(as: 'category', history: true)]
    public string $categoryFilter = '';

    #[Url(as: 'status', history: true)]
    public string $statusFilter = '';

    #[Url(as: 'priority', history: true)]
    public string $priorityFilter = '';

    #[Url(as: 'unit', history: true)]
    public string $departmentFilter = '';

    #[Url(as: 'personnel', history: true)]
    public string $personnelFilter = '';

    #[Url(as: 'assignment', history: true)]
    public string $assignmentFilter = ''; // '', 'unassigned', 'assigned'

    #[Url(as: 'view', history: true)]
    public string $viewMode = 'split'; // 'split', 'table', or 'map'

    #[Url(as: 'sort', history: true)]
    public string $sortField = 'created_at';

    #[Url(as: 'dir', history: true)]
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
        $this->personnelFilter = '';
        $this->resetPage();
    }

    public function updatedPersonnelFilter(): void
    {
        $this->resetPage();
    }

    public function updatedAssignmentFilter(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryFilter', 'statusFilter', 'priorityFilter', 'departmentFilter', 'personnelFilter', 'assignmentFilter']);
        $this->resetPage();
    }

    public function render()
    {
        // 1. Eager load relationships with selected columns to prevent N+1 and minimize memory footprint
        $query = Report::with([
            'category:id,name,color,icon',
            'user:id,name',
            'department:id,name,code',
            'personnel:id,name,position,department_id',
            'images',
        ])
            ->select([
                'id', 'reference_number', 'user_id', 'category_id', 'department_id',
                'personnel_id', 'assigned_to', 'title', 'priority', 'status', 'address_text', 'barangay',
                'latitude', 'longitude', 'created_at',
            ]);

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

        if (! empty($this->personnelFilter)) {
            $query->where('personnel_id', $this->personnelFilter);
        }

        if ($this->assignmentFilter === 'unassigned') {
            $query->whereNull('department_id')->whereNull('personnel_id');
        } elseif ($this->assignmentFilter === 'assigned') {
            $query->where(function ($q) {
                $q->whereNotNull('department_id')->orWhereNotNull('personnel_id');
            });
        }

        $query->orderBy($this->sortField, $this->sortDirection);

        // 2. Map markers: separate lightweight query selecting coordinates only (avoids hydrating images/histories for hundreds of markers)
        $mapReports = $this->formatMapMarkers(
            Report::query()
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->when(! empty($this->statusFilter), fn ($q) => $q->where('status', $this->statusFilter))
                ->when(! empty($this->categoryFilter), fn ($q) => $q->where('category_id', $this->categoryFilter))
                ->when(! empty($this->priorityFilter), fn ($q) => $q->where('priority', $this->priorityFilter))
                ->when(! empty($this->departmentFilter), fn ($q) => $q->where('department_id', $this->departmentFilter))
                ->when(! empty($this->personnelFilter), fn ($q) => $q->where('personnel_id', $this->personnelFilter))
                ->when($this->assignmentFilter === 'unassigned', fn ($q) => $q->whereNull('department_id')->whereNull('personnel_id'))
                ->when($this->assignmentFilter === 'assigned', fn ($q) => $q->where(fn ($sq) => $sq->whereNotNull('department_id')->orWhereNotNull('personnel_id')))
                ->select(['id', 'reference_number', 'title', 'category_id', 'department_id', 'personnel_id', 'status', 'priority', 'latitude', 'longitude', 'address_text'])
                ->with('category:id,name,is_hazardous')
                ->take(150)
                ->get()
        );

        $this->dispatch('sync-admin-map-markers', markers: $mapReports);

        // 3. Paginate table view (12 per page)
        $reports = $query->paginate(12);

        $selectedReport = $this->selectedReportId
            ? Report::with(['category', 'user', 'personnel.department', 'department', 'images', 'statusHistories.user'])
                ->find($this->selectedReportId)
            : null;

        $categories = ReportCategory::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        $personnelQuery = Personnel::where('is_active', true);
        if (! empty($this->departmentFilter)) {
            $personnelQuery->where('department_id', $this->departmentFilter);
        }
        $personnelList = $personnelQuery->orderBy('name')->get();

        return view('livewire.admin.report-management', [
            'reports' => $reports,
            'mapReports' => $mapReports,
            'selectedReport' => $selectedReport,
            'categories' => $categories,
            'departments' => $departments,
            'personnelList' => $personnelList,
        ]);
    }
}
