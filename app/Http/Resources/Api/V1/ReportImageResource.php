<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ReportImage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportImage
 */
class ReportImageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dimensions = $this->dimensions;

        return [
            'id' => $this->id,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'width' => $dimensions['width'] ?? null,
            'height' => $dimensions['height'] ?? null,
            'original_name' => $this->original_name,
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'is_resolution_proof' => (bool) $this->is_resolution_proof,
            'caption' => $this->caption,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
