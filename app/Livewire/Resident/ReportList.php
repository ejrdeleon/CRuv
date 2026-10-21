<?php

namespace App\Livewire\Resident;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.resident')]
class ReportList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Report::with(['category', 'images', 'publicComments'])
            ->where('user_id', Auth::id());

        if (! empty($this->search)) {
            $query->where(function ($q) {
                $q->where('reference_number', 'like', '%'.$this->search.'%')
                    ->orWhere('title', 'like', '%'.$this->search.'%')
                    ->orWhere('address_text', 'like', '%'.$this->search.'%');
            });
        }

        if ($this->statusFilter === 'active') {
            $query->whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress']);
        } elseif ($this->statusFilter === 'in_progress') {
            $query->where('status', 'in_progress');
        } elseif ($this->statusFilter === 'resolved') {
            $query->where('status', 'resolved');
        } elseif ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        $reports = $query->orderByDesc('created_at')->paginate(10);

        return view('livewire.resident.report-list', [
            'reports' => $reports,
        ]);
    }
}
