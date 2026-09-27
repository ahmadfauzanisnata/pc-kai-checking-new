<?php

namespace App\Models;

use App\Enums\InspectionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Inspection extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'public_id',
        'inspection_period_id',
        'employee_id',
        'device_id',
        'inspector_id',
        'reference_number',
        'document_date',
        'inspection_date',
        'status',
        'general_notes',
        'employee_signature_path',
        'submitted_at',
        'admin_verified_at',
        'approved_at',
        'locked_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => InspectionStatus::class,
            'document_date' => 'date',
            'inspection_date' => 'date',
            'submitted_at' => 'datetime',
            'admin_verified_at' => 'datetime',
            'approved_at' => 'datetime',
            'locked_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->public_id)) {
                $model->public_id = (string) Str::uuid();
            }
        });
    }

    public function inspectionPeriod(): BelongsTo
    {
        return $this->belongsTo(InspectionPeriod::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }

    public function checklist(): HasOne
    {
        return $this->hasOne(InspectionChecklist::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(InspectionNote::class)->orderBy('created_at', 'desc');
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(InspectionApproval::class)->orderBy('action_at', 'desc');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InspectionStatusHistory::class)->orderBy('created_at', 'desc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(InspectionAttachment::class)->orderBy('created_at', 'desc');
    }

    // Helper methods
    public function isLocked(): bool
    {
        return $this->status === InspectionStatus::LOCKED;
    }

    public function isApproved(): bool
    {
        return in_array($this->status, [
            InspectionStatus::APPROVED,
            InspectionStatus::LOCKED,
            InspectionStatus::ARCHIVED,
        ]);
    }

    public function isEditableByInspector(): bool
    {
        return in_array($this->status, [
            InspectionStatus::ASSIGNED,
            InspectionStatus::IN_PROGRESS,
            InspectionStatus::REVISION_REQUIRED,
        ]);
    }

    public function hasFindings(): bool
    {
        return $this->checklist?->hasNegativeFindings() ?? false;
    }

    public function findingsCount(): int
    {
        return $this->checklist?->countNegativeFindings() ?? 0;
    }
}
