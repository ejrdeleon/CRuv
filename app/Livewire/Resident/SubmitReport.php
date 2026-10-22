<?php

namespace App\Livewire\Resident;

use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Services\ReportPhotoUploader;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.resident')]
class SubmitReport extends Component
{
    use WithFileUploads;

    public ?int $category_id = null;

    #[Rule('required|string|min:5|max:200')]
    public string $title = '';

    #[Rule('required|string|min:15|max:3000')]
    public string $description = '';

    #[Rule('required|in:low,medium,high,urgent')]
    public string $priority = 'medium';

    #[Rule('required|numeric|between:-90,90')]
    public ?float $latitude = 14.2816;

    #[Rule('required|numeric|between:-180,180')]
    public ?float $longitude = 121.4146;

    #[Rule('required|string|min:5|max:255')]
    public string $address_text = '';

    #[Rule('required|string|max:100')]
    public string $barangay = 'Poblacion I';

    #[Rule('required|string|max:100')]
    public string $city = 'Santa Cruz';

    #[Rule('required|string|max:100')]
    public string $province = 'Laguna';

    public string $landmark = '';

    public bool $is_hazard_warning_acknowledged = false;

    /** @var array<TemporaryUploadedFile> */
    public array $photos = [];

    // Submission modal confirmation & result
    public bool $showConfirmModal = false;

    public ?Report $submittedReport = null;

    public function mount(?int $category_id = null)
    {
        if ($category_id) {
            $this->category_id = $category_id;
            $this->updatedCategoryId($category_id);
        } else {
            $first = ReportCategory::where('is_active', true)->first();
            if ($first) {
                $this->category_id = $first->id;
                $this->priority = $first->default_priority;
            }
        }

        $user = Auth::user();
        if ($user && $user->barangay) {
            $this->barangay = $user->barangay;
        }
        if ($user && $user->address) {
            $this->address_text = $user->address;
        }
    }

    public function updatedCategoryId($value)
    {
        $cat = ReportCategory::find($value);
        if ($cat) {
            $this->priority = $cat->default_priority;
            if (! $cat->is_hazardous) {
                $this->is_hazard_warning_acknowledged = true;
            } else {
                $this->is_hazard_warning_acknowledged = false;
            }
        }
    }

    public function removePhoto($index)
    {
        if (isset($this->photos[$index])) {
            unset($this->photos[$index]);
            $this->photos = array_values($this->photos);
        }
    }

    public function setCoordinates($lat, $lng)
    {
        $this->latitude = (float) $lat;
        $this->longitude = (float) $lng;
    }

    public function openConfirmModal()
    {
        $category = ReportCategory::find($this->category_id);

        $rules = [
            'category_id' => 'required|exists:report_categories,id',
            'title' => 'required|string|min:5|max:200',
            'description' => 'required|string|min:15|max:3000',
            'priority' => 'required|in:low,medium,high,urgent',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'address_text' => 'required|string|min:5|max:255',
            'barangay' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'province' => 'required|string|max:100',
            'photos.*' => 'nullable|image|max:10240', // 10MB max per photo
        ];

        if ($category && $category->is_hazardous) {
            $rules['is_hazard_warning_acknowledged'] = 'accepted';
        }

        $this->validate($rules, [
            'is_hazard_warning_acknowledged.accepted' => 'You must acknowledge the safety warning regarding fallen/exposed electrical lines and hazards before submitting.',
        ]);

        $this->showConfirmModal = true;
    }

    public function submit()
    {
        $this->validate();

        $category = ReportCategory::findOrFail($this->category_id);

        if ($category->is_hazardous && ! $this->is_hazard_warning_acknowledged) {
            $this->addError('is_hazard_warning_acknowledged', 'Please acknowledge the safety advisory.');

            return;
        }

        $user = Auth::user();
        $referenceNumber = Report::generateReferenceNumber();

        $report = Report::create([
            'reference_number' => $referenceNumber,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'department_id' => $category->department_id,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => 'submitted',
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address_text' => $this->address_text,
            'barangay' => $this->barangay,
            'city' => $this->city,
            'province' => $this->province,
            'landmark' => $this->landmark ?: null,
            'is_hazard_warning_acknowledged' => $this->is_hazard_warning_acknowledged,
            'submitted_at' => now(),
        ]);

        // Upload photos (resized and stored via ReportPhotoUploader)
        if (! empty($this->photos)) {
            $uploader = app(ReportPhotoUploader::class);
            foreach ($this->photos as $photo) {
                $uploader->storeAndAttach($report, $photo, false, 'Resident photo attachment');
            }
        }

        // Record initial status history
        ReportStatusHistory::create([
            'report_id' => $report->id,
            'user_id' => $user->id,
            'from_status' => null,
            'to_status' => 'submitted',
            'comment' => 'Report submitted by resident.',
        ]);

        // Create resident notification
        Notification::create([
            'user_id' => $user->id,
            'report_id' => $report->id,
            'title' => 'Report Received',
            'message' => "Your report #{$report->reference_number} ('{$report->title}') has been received by ".config('community.full_location', 'Santa Cruz, Laguna, Philippines').'.',
            'type' => 'report_submitted',
            'is_read' => false,
        ]);

        // Notify barangay admin
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $adminUser) {
            Notification::create([
                'user_id' => $adminUser->id,
                'report_id' => $report->id,
                'title' => 'New Community Report',
                'message' => "New {$report->priority} priority report: {$report->title} in {$report->barangay}.",
                'type' => 'new_report',
                'is_read' => false,
            ]);
        }

        ActivityLog::log(
            'report_submitted',
            "Resident {$user->name} submitted report {$report->reference_number}",
            $report,
            ['category' => $category->name, 'priority' => $report->priority],
            $user
        );

        $this->submittedReport = $report;
        $this->showConfirmModal = false;
    }

    public function render()
    {
        $categories = ReportCategory::where('is_active', true)->get();
        $selectedCategory = $categories->firstWhere('id', $this->category_id);

        return view('livewire.resident.submit-report', [
            'categories' => $categories,
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
