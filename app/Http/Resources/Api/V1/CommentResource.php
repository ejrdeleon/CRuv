<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ReportComment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportComment
 */
class CommentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'comment' => $this->comment,
            'author_name' => $this->whenLoaded('user', fn () => $this->user?->name ?? 'Barangay Staff', 'Barangay Staff'),
            'author_role' => $this->whenLoaded('user', fn () => $this->user?->role ?? 'admin', 'admin'),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
