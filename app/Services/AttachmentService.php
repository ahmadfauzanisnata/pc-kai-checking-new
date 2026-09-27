<?php

namespace App\Services;

use App\Enums\AttachmentType;
use App\Models\Inspection;
use App\Models\InspectionAttachment;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    protected static array $allowedMimes = [
        'image/jpeg',
        'image/png',
        'image/jpg',
        'application/pdf',
    ];

    /**
     * Upload an attachment for an inspection
     */
    public function upload(
        Inspection $inspection,
        UploadedFile $file,
        AttachmentType $type,
        User $user,
        ?string $description = null
    ): InspectionAttachment {
        $mime = $file->getMimeType();
        if (!in_array($mime, self::$allowedMimes)) {
            throw new Exception("Format berkas tidak diizinkan. Hanya file JPG, PNG, atau PDF yang diperbolehkan.");
        }

        // Limit 10MB
        if ($file->getSize() > 10 * 1024 * 1024) {
            throw new Exception("Ukuran berkas melebihi batas maksimal 10MB.");
        }

        $extension = $file->getClientOriginalExtension();
        $storedName = Str::random(40) . '.' . $extension;
        $filePath = $file->storeAs('attachments/' . $inspection->id, $storedName, 'public');

        $attachment = InspectionAttachment::create([
            'inspection_id' => $inspection->id,
            'uploaded_by' => $user->id,
            'attachment_type' => $type,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => $storedName,
            'file_path' => $filePath,
            'mime_type' => $mime,
            'file_size' => $file->getSize(),
            'description' => $description,
            'created_at' => now(),
        ]);

        ActivityLogService::log(
            'unggah berkas',
            'Lampiran',
            "Mengunggah berkas lampiran {$attachment->original_name} pada pemeriksaan {$inspection->reference_number}",
            $inspection,
            null,
            null,
            $user->id
        );

        return $attachment;
    }

    /**
     * Delete an attachment
     */
    public function delete(InspectionAttachment $attachment, User $user): bool
    {
        if (Storage::disk('public')->exists($attachment->file_path)) {
            Storage::disk('public')->delete($attachment->file_path);
        }

        ActivityLogService::log(
            'hapus berkas',
            'Lampiran',
            "Menghapus berkas {$attachment->original_name}",
            null,
            null,
            null,
            $user->id
        );

        return $attachment->delete();
    }
}
