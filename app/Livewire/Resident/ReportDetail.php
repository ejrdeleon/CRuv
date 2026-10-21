<?php

namespace App\Livewire\Resident;

use App\Models\ActivityLog;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.resident')]
class ReportDetail extends Component
{
    public Report $report;

    public bool $isEditing = false;

    public string $editTitle = '';

    public string $editDescription = '';

    public string $editLandmark = '';

    public ?string $selectedPhotoUrl = null;

    public function mount(Report $report)
    {
        $user = Auth::user();

        // Enforce authorization
        if (! $user->isAdmin() && $report->user_id !== $user->id) {
            abort(403, 'You are not authorized to view this private report.');
        }

        $this->report = $report->load(['category', 'department', 'assignedStaff', 'images', 'statusHistories.user', 'publicComments.user']);
        $this->editTitle = $report->title;
        $this->editDescription = $report->description;
        $this->editLandmark = $report->landmark ?? '';
    }

    public function startEdit()
    {
        $this->report->refresh();

        if (! $this->report->canBeEditedBy(Auth::user())) {
            session()->flash('error', 'This report is already under review or processed and can no longer be edited.');

            return;
        }

        $this->isEditing = true;
    }

    public function cancelEdit()
    {
        $this->isEditing = false;
        $this->editTitle = $this->report->title;
        $this->editDescription = $this->report->description;
        $this->editLandmark = $this->report->landmark ?? '';
    }

    public function saveEdit()
    {
        if (! $this->report->canBeEditedBy(Auth::user())) {
            session()->flash('error', 'Cannot edit report.');
            $this->isEditing = false;

            return;
        }

        $this->validate([
            'editTitle' => 'required|string|min:5|max:200',
            'editDescription' => 'required|string|min:15|max:3000',
            'editLandmark' => 'nullable|string|max:255',
        ]);

        $this->report->update([
            'title' => $this->editTitle,
            'description' => $this->editDescription,
            'landmark' => $this->editLandmark ?: null,
        ]);

        ActivityLog::log(
            'report_updated_by_resident',
            "Resident {$this->report->user->name} updated description on {$this->report->reference_number}",
            $this->report,
            null,
            Auth::user()
        );

        $this->isEditing = false;
        session()->flash('success', 'Report details updated successfully.');
    }

    public function openPhotoModal(string $url)
    {
        $this->selectedPhotoUrl = $url;
    }

    public function closePhotoModal()
    {
        $this->selectedPhotoUrl = null;
    }

    public function render()
    {
        return view('livewire.resident.report-detail');
    }
}
