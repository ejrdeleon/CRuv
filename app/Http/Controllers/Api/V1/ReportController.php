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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Report::class);

        /** @var User $user */
        $user = $request->user();

        // 1. Eager load only needed relationships with selected columns (prevents N+1 and minimizes memory)
        $query = Report::with([
            'category:id,name,slug,description,is_hazardous',
            'department:id,name,code',
            'assignedStaff:id,name',
            'images',
        ])
            ->select([
                'id', 'reference_number', 'user_id', 'category_id', 'department_id',
                'assigned_to', 'title', 'description', 'priority', 'status',
                'rejection_reason', 'resolution_notes', 'latitude', 'longitude',
                'address_text', 'barangay', 'city', 'province', 'landmark',
                'is_hazard_warning_acknowledged', 'submitted_at', 'verified_at',
                'assigned_at', 'in_progress_at', 'resolved_at', 'created_at',
            ])
            ->where('user_id', $user->id);

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

        // 2. Paginate: never load unbounded rows into memory
        $perPage = min(50, max(5, $request->integer('per_page', 20)));
        $reports = $query->orderByDesc('created_at')->paginate($perPage);

        // 3. Consolidated single-query aggregation for user statistics (1 query instead of 3 count queries)
        $stats = DB::table('reports')
            ->where('user_id', $user->id)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status IN ('submitted', 'under_review', 'verified', 'assigned', 'in_progress') THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved
            ")->first();

        // 4. Cached community map pins with 60s TTL (lightweight marker attributes only)
        $communityPins = Cache::remember('api:community_pins', 60, function () {
            return Report::with('category:id,name,is_hazardous')
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->whereNotIn('status', ['rejected', 'duplicate'])
                ->select(['id', 'reference_number', 'title', 'category_id', 'status', 'priority', 'latitude', 'longitude', 'address_text'])
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
                ])
                ->values()
                ->all();
        });

        // 5. Cached active safety alert count (60s TTL)
        $safetyAlertsCount = Cache::remember('api:safety_alerts_count', 60, function () {
            return Report::whereIn('priority', ['urgent', 'high'])
                ->whereNotIn('status', ['resolved', 'rejected', 'duplicate'])
                ->count();
        });

        return response()->json([
            'data' => ReportResource::collection($reports),
            'meta' => [
                'stats' => [
                    'total' => (int) ($stats->total ?? 0),
                    'active' => (int) ($stats->active ?? 0),
                    'resolved' => (int) ($stats->resolved ?? 0),
                    'safety_alerts' => $safetyAlertsCount,
                ],
                'pagination' => [
                    'current_page' => $reports->currentPage(),
                    'last_page' => $reports->lastPage(),
                    'per_page' => $reports->perPage(),
                    'total' => $reports->total(),
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

        // Invalidate cached aggregates so dashboards and maps refresh instantly
        Cache::forget('api:community_pins');
        Cache::forget('api:safety_alerts_count');
        Cache::forget('admin:dashboard:metrics');

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
