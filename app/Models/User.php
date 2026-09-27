<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'role_id',
        'business_area_id',
        'unit_id',
        'name',
        'nipp',
        'email',
        'password',
        'phone',
        'signature_path',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function businessArea(): BelongsTo
    {
        return $this->belongsTo(BusinessArea::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function assignedInspections(): HasMany
    {
        return $this->hasMany(Inspection::class, 'inspector_id');
    }

    public function inspectionNotes(): HasMany
    {
        return $this->hasMany(InspectionNote::class);
    }

    public function inspectionApprovals(): HasMany
    {
        return $this->hasMany(InspectionApproval::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(InspectionStatusHistory::class, 'changed_by');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    // Role helper methods
    public function isSuperAdmin(): bool
    {
        return $this->role?->name === UserRole::SUPER_ADMIN->value;
    }

    public function isAdmin(): bool
    {
        return $this->role?->name === UserRole::ADMIN->value;
    }

    public function isInspector(): bool
    {
        return $this->role?->name === UserRole::INSPECTOR->value;
    }

    public function hasRole(string|UserRole $role): bool
    {
        $roleName = $role instanceof UserRole ? $role->value : $role;
        return $this->role?->name === $roleName;
    }

    public function getRoleDisplayNameAttribute(): string
    {
        return $this->role?->display_name ?? 'Pengguna';
    }
}
