<?php

namespace App\Models;

use App\Enums\AttachmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class InspectionAttachment extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'inspection_id',
        'uploaded_by',
        'attachment_type',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'attachment_type' => AttachmentType::class,
            'file_size' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function inspection(): BelongsTo
    {
        return $this->belongsTo(Inspection::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): string
    {
        return Storage::url($this->file_path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }
}
