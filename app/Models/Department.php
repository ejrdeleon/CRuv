<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'contact_person',
        'contact_phone',
        'contact_email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function staff(): HasMany
    {
        return $this->hasMany(User::class, 'department_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'department_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(ReportCategory::class, 'department_id');
    }
}
