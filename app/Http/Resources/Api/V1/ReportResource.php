<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Report
 */
class ReportResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference_number' => $this->reference_number,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority,
            'status' => $this->status,
            'status_label' => $this->status_label,
            'rejection_reason' => $this->rejection_reason,
            'resolution_notes' => $this->resolution_notes,
            'latitude' => (float) $this->latitude,
            'longitude' => (float) $this->longitude,
            'address_text' => $this->address_text,
            'barangay' => $this->barangay,
            'city' => $this->city,
            'province' => $this->province,
            'landmark' => $this->landmark,
            'is_hazard_warning_acknowledged' => (bool) $this->is_hazard_warning_acknowledged,
            'can_edit' => $this->canBeEditedBy($request->user()),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'department_name' => $this->whenLoaded('department', fn () => $this->department?->name),
            'assigned_staff_name' => $this->whenLoaded('assignedStaff', fn () => $this->assignedStaff?->name),
            'assigned_to_name' => $this->whenLoaded('assignedStaff', fn () => $this->assignedStaff?->name),
            'images' => ReportImageResource::collection($this->whenLoaded('images')),
            'status_histories' => StatusHistoryResource::collection($this->whenLoaded('statusHistories')),
            'public_comments' => CommentResource::collection($this->whenLoaded('publicComments')),
            'submitted_at' => $this->submitted_at?->toIso8601String() ?? $this->created_at?->toIso8601String(),
            'verified_at' => $this->verified_at?->toIso8601String(),
            'assigned_at' => $this->assigned_at?->toIso8601String(),
            'in_progress_at' => $this->in_progress_at?->toIso8601String(),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
