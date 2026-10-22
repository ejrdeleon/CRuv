<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreReportRequest;
use App\Http\Requests\Api\V1\UpdateReportRequest;
use App\Http\Resources\Api\V1\ReportResource;
use App\Models\ActivityLog;
use App\Models\Notification;
use App\Models\Report;
use App\Models\ReportCategory;
use App\Models\ReportStatusHistory;
use App\Models\User;
use App\Services\ReportPhotoUploader;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        /** @var User $user */
        $user = $request->user();

        $query = Report::with([
            'category.department',
            'department',
            'assignedStaff',
            'images',
            'publicComments.user',
        ])->where('user_id', $user->id);

        $statusFilter = $request->string('status')->trim()->toString();
        if ($statusFilter !== '' && $statusFilter !== 'all') {
            if ($statusFilter === 'active') {
                $query->whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress']);
            } else {
                $query->where('status', $statusFilter);
            }
        }

        $search = $request->string('search')->trim()->toString();
        if ($search !== '') {
            $query->where(function ($subQuery) use ($search): void {
                $subQuery->where('reference_number', 'like', '%'.$search.'%')
                    ->orWhere('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%')
                    ->orWhere('address_text', 'like', '%'.$search.'%');
            });
        }

        $reports = $query->orderByDesc('created_at')->get();

        $totalCount = $user->reports()->count();
        $activeCount = $user->reports()
            ->whereIn('status', ['submitted', 'under_review', 'verified', 'assigned', 'in_progress'])
            ->count();
        $resolvedCount = $user->reports()->where('status', 'resolved')->count();

        // Nearby community pins for the Citizen Bento Map + active safety alerts
        $communityPins = Report::with('category')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereNotIn('status', ['rejected', 'duplicate'])
            ->orderByDesc('created_at')
            ->take(40)
            ->get()
            ->map(fn (Report $r) => [
                'id' => $r->id,
                'reference_number' => $r->reference_number,
                'title' => $r->title,
                'category_name' => $r->category?->name ?? 'Community Issue',
                'status' => $r->status,
                'status_label' => $r->status_label,
                'priority' => $r->priority,
                'latitude' => (float) $r->latitude,
                'longitude' => (float) $r->longitude,
                'address_text' => $r->address_text,
                'is_hazardous' => (bool) ($r->category?->is_hazardous ?? false),
            ]);

        $safetyAlertsCount = Report::whereIn('priority', ['urgent', 'high'])
            ->whereNotIn('status', ['resolved', 'rejected', 'duplicate'])
            ->count();

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'stats' => [
                    'total' => $totalCount,
                    'active' => $activeCount,
                    'resolved' => $resolvedCount,
                    'safety_alerts' => $safetyAlertsCount,
                ],
                'community_pins' => $communityPins,
            ],
        ]);
    }

    public function store(StoreReportRequest $request, ReportPhotoUploader $photoUploader): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $validated = $request->validated();

        $category = ReportCategory::findOrFail($validated['category_id']);
        $referenceNumber = Report::generateReferenceNumber();

        $report = Report::create([
            'reference_number' => $referenceNumber,
            'user_id' => $user->id,
            'category_id' => $category->id,
            'department_id' => $category->department_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'] ?? $category->default_priority ?? 'medium',
            'status' => 'submitted',
            'latitude' => (float) $validated['latitude'],
            'longitude' => (float) $validated['longitude'],
            'address_text' => $validated['address_text'],
            'barangay' => $validated['barangay'] ?? $user->barangay ?? config('community.barangays.0', 'Poblacion I'),
            'city' => $validated['city'] ?? config('community.municipality', 'Santa Cruz'),
            'province' => $validated['province'] ?? config('community.province', 'Laguna'),
            'landmark' => $validated['landmark'] ?? null,
            'is_hazard_warning_acknowledged' => (bool) ($validated['is_hazard_warning_acknowledged'] ?? ! $category->is_hazardous),
            'submitted_at' => now(),
        ]);

        /** @var array<int, UploadedFile> $uploadedFiles */
        $uploadedFiles = [];
        if ($request->hasFile('photo')) {
            $singleFile = $request->file('photo');
            if ($singleFile instanceof UploadedFile) {
                $uploadedFiles[] = $singleFile;
            }
        }
        if ($request->hasFile('photos')) {
            foreach ((array) $request->file('photos') as $file) {
                if ($file instanceof UploadedFile) {
                    $uploadedFiles[] = $file;
                }
            }
        }

        foreach ($uploadedFiles as $file) {
            $photoUploader->storeAndAttach($report, $file, false, 'Resident photo attachment');
        }

        ReportStatusHistory::create([
            'report_id' => $report->id,
            'user_id' => $user->id,
            'from_status' => null,
            'to_status' => 'submitted',
            'comment' => 'Report submitted by resident.',
        ]);

        Notification::create([
            'user_id' => $user->id,
            'report_id' => $report->id,
            'title' => 'Report Received',
            'message' => "Your report #{$report->reference_number} ('{$report->title}') has been received by ".config('community.full_location', 'Santa Cruz, Laguna, Philippines').'.',
            'type' => 'report_submitted',
            'is_read' => false,
        ]);

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

        $report->load([
            'category.department',
            'department',
            'assignedStaff',
            'images',
            'statusHistories.user',
            'publicComments.user',
        ]);

        return response()->json([
            'message' => 'Incident report submitted successfully.',
            'data' => new ReportResource($report),
        ], 201);
    }

    public function show(Request $request, Report $report): JsonResponse
    {
        Gate::authorize('view', $report);

        $report->load([
            'category.department',
            'department',
            'assignedStaff',
            'images',
            'statusHistories.user',
            'publicComments.user',
        ]);

        return response()->json([
            'data' => new ReportResource($report),
        ]);
    }

    public function update(UpdateReportRequest $request, Report $report): JsonResponse
    {
        Gate::authorize('update', $report);

        $validated = $request->validated();

        $report->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'landmark' => $validated['landmark'] ?? null,
        ]);

        ActivityLog::log(
            'report_edited',
            "Resident {$request->user()->name} updated report #{$report->reference_number}",
            $report,
            null,
            $request->user()
        );

        $report->load([
            'category.department',
            'department',
            'assignedStaff',
            'images',
            'statusHistories.user',
            'publicComments.user',
        ]);

        return response()->json([
            'message' => 'Report details updated successfully.',
            'data' => new ReportResource($report),
        ]);
    }
}
