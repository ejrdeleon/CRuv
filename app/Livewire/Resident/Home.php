<?php

namespace App\Livewire\Resident;

use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportCategory;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.resident')]
class Home extends Component
{
    public string $trackReference = '';

    public ?string $trackError = null;

    public function trackReport()
    {
        $ref = trim($this->trackReference);
        if (empty($ref)) {
            $this->trackError = 'Please enter a report reference number.';

            return;
        }

        $report = Report::where('reference_number', $ref)->first();
        if (! $report) {
            $this->trackError = "Report with reference '{$ref}' was not found.";

            return;
        }

        // Check if user is authorized to view
        if (! Auth::user()->isAdmin() && ! Auth::user()->isStaff() && $report->user_id !== Auth::id()) {
            $this->trackError = 'You do not have permission to view this private report.';

            return;
        }

        return redirect()->route('resident.reports.show', $report->id);
    }

    public function render()
    {
        $user = Auth::user();

        $recentReports = Report::with(['category', 'images'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        $stats = [
            'total' => Report::where('user_id', $user->id)->count(),
            'active' => Report::where('user_id', $user->id)->whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress'])->count(),
            'resolved' => Report::where('user_id', $user->id)->where('status', 'resolved')->count(),
        ];

        $recentNotifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->take(3)
            ->get();

        $categories = ReportCategory::where('is_active', true)->get();

        return view('livewire.resident.home', [
            'recentReports' => $recentReports,
            'stats' => $stats,
            'recentNotifications' => $recentNotifications,
            'categories' => $categories,
        ]);
    }
}
