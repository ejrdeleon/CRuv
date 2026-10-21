<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference_number',
        'user_id',
        'category_id',
        'department_id',
        'assigned_to',
        'duplicate_of_id',
        'title',
        'description',
        'priority',
        'status',
        'rejection_reason',
        'latitude',
        'longitude',
        'address_text',
        'barangay',
        'city',
        'province',
        'landmark',
        'is_hazard_warning_acknowledged',
        'submitted_at',
        'verified_at',
        'assigned_at',
        'in_progress_at',
        'resolved_at',
        'resolution_notes',
        'resolution_image_path',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'is_hazard_warning_acknowledged' => 'boolean',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'assigned_at' => 'datetime',
            'in_progress_at' => 'datetime',
            'resolved_at' => 'datetime',
        ];
    }

    public static function generateReferenceNumber(): string
    {
        $prefix = 'BRGY-'.date('Ymd');
        $latest = static::where('reference_number', 'LIKE', $prefix.'-%')
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->reference_number);
            $seq = isset($parts[2]) ? (int) $parts[2] + 1 : 1;
        } else {
            $seq = 1;
        }

        return sprintf('%s-%04d', $prefix, $seq);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ReportCategory::class, 'category_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function duplicateOf(): BelongsTo
    {
        return $this->belongsTo(Report::class, 'duplicate_of_id');
    }

    public function duplicates(): HasMany
    {
        return $this->hasMany(Report::class, 'duplicate_of_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReportImage::class, 'report_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ReportStatusHistory::class, 'report_id')->orderBy('created_at', 'asc');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'report_id')->orderBy('created_at', 'asc');
    }

    public function publicComments(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'report_id')->where('is_internal', false)->orderBy('created_at', 'asc');
    }

    public function internalComments(): HasMany
    {
        return $this->hasMany(ReportComment::class, 'report_id')->where('is_internal', true)->orderBy('created_at', 'asc');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class, 'report_id');
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        // Resident can edit only if status is submitted (not yet under review/verified)
        return $this->user_id === $user->id && $this->status === 'submitted';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'verified' => 'Verified',
            'assigned' => 'Assigned',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'rejected' => 'Rejected',
            'duplicate' => 'Duplicate',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }
}
