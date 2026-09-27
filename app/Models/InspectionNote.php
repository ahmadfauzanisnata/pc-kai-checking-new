<?php

namespace App\Models;

use App\Enums\NoteType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_id',
        'user_id',
        'note_type',
        'note',
        'is_internal',
    ];

    protected function casts(): array
    {
        return [
            'note_type' => NoteType::class,
            'is_internal' => 'boolean',
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
