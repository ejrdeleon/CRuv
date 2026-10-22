<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ReportCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ReportCategory
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'color' => $this->color,
            'default_priority' => $this->default_priority,
            'is_hazardous' => (bool) $this->is_hazardous,
            'safety_advisory' => $this->safety_advisory,
            'department_name' => $this->whenLoaded('department', fn () => $this->department?->name),
        ];
    }
}
