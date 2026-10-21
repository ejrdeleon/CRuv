<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReportCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'default_priority',
        'department_id',
        'is_hazardous',
        'safety_advisory',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_hazardous' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'category_id');
    }
}
