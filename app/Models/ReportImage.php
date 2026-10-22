<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
