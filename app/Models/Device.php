<?php

namespace App\Models;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'asset_number',
        'hostname',
        'device_type',
        'brand',
        'model',
        'serial_number',
        'ip_address',
        'operating_system',
        'os_version',
        'device_status',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'device_type' => DeviceType::class,
            'device_status' => DeviceStatus::class,
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(Inspection::class);
    }
}
