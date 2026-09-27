<?php

namespace App\Models;

use App\Enums\ApprovalDecision;
use App\Enums\ApprovalLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'user_id',
        'approval_level',
        'decision',
        'comment',
        'signature_snapshot',
        'signature_type',
        'action_at',
    ];

    public function isManual(): bool
    {
        return $this->signature_type === 'manual';
    }

    public function isDigital(): bool
    {
        return $this->signature_type !== 'manual';
    }

    protected function casts(): array
    {
        return [
            'approval_level' => ApprovalLevel::class,
            'decision' => ApprovalDecision::class,
            'action_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
