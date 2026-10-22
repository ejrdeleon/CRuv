<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ReportStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportStatusHistory
 */
class StatusHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'from_status' => $this->from_status,
            'to_status' => $this->to_status,
            'to_status_label' => ucfirst(str_replace('_', ' ', (string) $this->to_status)),
            'comment' => $this->comment,
            'actor_name' => $this->whenLoaded('user', fn () => $this->user?->name ?? 'Barangay Office', 'Barangay Office'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
