<?php

namespace App\Models;

use App\Enums\InspectionPeriodStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InspectionPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_area_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'locked_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => InspectionPeriodStatus::class,
            'start_date' => 'date',
            'end_date' => 'date',
            'locked_at' => 'datetime',
        ];
    }

    public function businessArea(): BelongsTo
    {
        return $this->belongsTo(BusinessArea::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }

    public function isLocked(): bool
    {
        return $this->status === InspectionPeriodStatus::LOCKED;
    }

    public function isActive(): bool
    {
        return $this->status === InspectionPeriodStatus::ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === InspectionPeriodStatus::DRAFT;
    }
}
