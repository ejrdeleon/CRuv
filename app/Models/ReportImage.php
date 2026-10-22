<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ReportImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'is_resolution_proof',
        'caption',
    ];

    protected function casts(): array
    {
        return [
            'is_resolution_proof' => 'boolean',
            'file_size' => 'integer',
        ];
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function getUrlAttribute(): string
    {
        return url('storage/'.ltrim($this->file_path, '/'));
    }

    public function getThumbnailUrlAttribute(): string
    {
        $thumbnailPath = str_replace('reports/', 'reports/thumbnails/', $this->file_path);
        if (Storage::disk('public')->exists($thumbnailPath)) {
            return url('storage/'.ltrim($thumbnailPath, '/'));
        }

        return $this->url;
    }

    /**
     * Read actual pixel dimensions from file when needed without schema modifications.
     *
     * @return array{width: int, height: int}|null
     */
    public function getDimensionsAttribute(): ?array
    {
        try {
            $fullPath = Storage::disk('public')->path($this->file_path);
            if (file_exists($fullPath)) {
                $size = @getimagesize($fullPath);
                if (is_array($size) && isset($size[0], $size[1])) {
                    return ['width' => (int) $size[0], 'height' => (int) $size[1]];
                }
            }
        } catch (\Throwable) {
            // Safe fallback
        }

        return null;
    }

    public function getIsLandscapeAttribute(): bool
    {
        $dim = $this->dimensions;

        return $dim ? ($dim['width'] > $dim['height']) : true;
    }

    public function getIsPortraitAttribute(): bool
    {
        $dim = $this->dimensions;

        return $dim ? ($dim['height'] > $dim['width']) : false;
    }
}
