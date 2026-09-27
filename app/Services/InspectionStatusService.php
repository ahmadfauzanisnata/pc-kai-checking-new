<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\ApprovalLevel;
use App\Enums\InspectionStatus;
use App\Enums\NoteType;
use App\Models\Inspection;
use App\Models\InspectionApproval;
use App\Models\InspectionNote;
use App\Models\InspectionStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

class InspectionStatusService
{
    /**
     * Start inspection (ASSIGNED -> IN_PROGRESS)
     */
    public function startInspection(Inspection $inspection, User $user): Inspection
    {
        if ($inspection->status !== InspectionStatus::ASSIGNED) {
            throw new Exception("Status pemeriksaan saat ini ({$inspection->status->label()}) tidak dapat dimulai.");
        }

        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            throw new Exception("Anda tidak memiliki akses untuk memulai pemeriksaan tugas ini.");
        }

        return DB::transaction(function () use ($inspection, $user) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::IN_PROGRESS;
            $inspection->inspection_date = $inspection->inspection_date ?? now()->toDateString();
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::IN_PROGRESS, 'start', 'Pemeriksa memulai pemeriksaan perangkat.');
            ActivityLogService::log('mulai pemeriksaan', 'Pemeriksaan', "Memulai pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Submit inspection to admin (IN_PROGRESS -> PENDING_ADMIN_VERIFICATION)
     */
    public function submitInspection(Inspection $inspection, User $user, ?string $comment = null): Inspection
    {
        if ($inspection->status !== InspectionStatus::IN_PROGRESS) {
            throw new Exception("Hanya pemeriksaan yang berstatus Sedang Diperiksa yang dapat dikirim.");
        }

        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            throw new Exception("Anda tidak berwenang mengirimkan pemeriksaan ini.");
        }

        $checklist = $inspection->checklist;
        if (!$checklist || !$checklist->isComplete()) {
            throw new Exception("Seluruh 12 butir checklist wajib diisi lengkap sebelum dikirim ke Admin.");
        }

        if ($checklist->hasNegativeFindings() && empty(trim($checklist->finding_summary ?? ''))) {
            throw new Exception("Terdapat temuan negatif pada checklist. Ringkasan temuan wajib diisi.");
        }

        return DB::transaction(function () use ($inspection, $user, $comment) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::PENDING_ADMIN_VERIFICATION;
            $inspection->submitted_at = now();
            $inspection->inspection_date = $inspection->inspection_date ?? now()->toDateString();
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::PENDING_ADMIN_VERIFICATION, 'submit', $comment ?? 'Hasil pemeriksaan dikirim kepada Admin.');

            InspectionApproval::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'approval_level' => ApprovalLevel::INSPECTOR,
                'decision' => ApprovalDecision::SUBMITTED,
                'comment' => $comment ?? 'Pemeriksaan selesai dan dikirim.',
                'action_at' => now(),
            ]);

            ActivityLogService::log('mengirim pemeriksaan', 'Pemeriksaan', "Mengirim hasil pemeriksaan {$inspection->reference_number} ke Admin", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Resubmit inspection by Inspector (REVISION_REQUIRED -> RESUBMITTED)
     */
    public function resubmitInspection(Inspection $inspection, User $user, string $notes): Inspection
    {
        if ($inspection->status !== InspectionStatus::REVISION_REQUIRED) {
            throw new Exception("Hanya pemeriksaan dengan status Perlu Perbaikan yang dapat dikirim ulang.");
        }

        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            throw new Exception("Anda tidak berwenang mengirim ulang pemeriksaan ini.");
        }

        if (empty(trim($notes))) {
            throw new Exception("Catatan perbaikan wajib diisi saat mengirim ulang.");
        }

        return DB::transaction(function () use ($inspection, $user, $notes) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::RESUBMITTED;
            $inspection->submitted_at = now();
            $inspection->save();

            InspectionNote::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'note_type' => NoteType::GENERAL,
                'note' => "Perbaikan telah dilakukan: {$notes}",
                'is_internal' => false,
            ]);

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::RESUBMITTED, 'resubmit', $notes);
            ActivityLogService::log('mengirim ulang', 'Pemeriksaan', "Mengirim ulang perbaikan pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Admin requests revision (PENDING_ADMIN_VERIFICATION / RESUBMITTED -> REVISION_REQUIRED)
     */
    public function requestRevisionByAdmin(Inspection $inspection, User $user, string $reason, array $flaggedItems = []): Inspection
    {
        if (!in_array($inspection->status, [InspectionStatus::PENDING_ADMIN_VERIFICATION, InspectionStatus::RESUBMITTED])) {
            throw new Exception("Status pemeriksaan saat ini tidak dapat dikembalikan untuk revisi.");
        }

        if (empty(trim($reason))) {
            throw new Exception("Alasan revisi wajib diisi oleh Admin.");
        }

        return DB::transaction(function () use ($inspection, $user, $reason, $flaggedItems) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::REVISION_REQUIRED;
            $inspection->save();

            InspectionNote::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'note_type' => NoteType::ADMIN_REVISION,
                'note' => $reason,
                'is_internal' => false,
            ]);

            $metadata = !empty($flaggedItems) ? ['flagged_items' => $flaggedItems] : null;

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::REVISION_REQUIRED, 'request_revision', $reason, $metadata);
            ActivityLogService::log('meminta revisi', 'Pemeriksaan', "Admin meminta perbaikan pemeriksaan {$inspection->reference_number}: {$reason}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Admin verifies inspection (PENDING_ADMIN_VERIFICATION / RESUBMITTED -> ADMIN_VERIFIED)
     */
    public function verifyByAdmin(Inspection $inspection, User $user, ?string $comment = null): Inspection
    {
        if (!in_array($inspection->status, [InspectionStatus::PENDING_ADMIN_VERIFICATION, InspectionStatus::RESUBMITTED])) {
            throw new Exception("Pemeriksaan tidak dalam status menunggu verifikasi.");
        }

        return DB::transaction(function () use ($inspection, $user, $comment) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::ADMIN_VERIFIED;
            $inspection->admin_verified_at = now();
            $inspection->save();

            InspectionApproval::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'approval_level' => ApprovalLevel::ADMIN,
                'decision' => ApprovalDecision::VERIFIED,
                'comment' => $comment ?? 'Hasil pemeriksaan telah diverifikasi sesuai ketentuan.',
                'action_at' => now(),
            ]);

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::ADMIN_VERIFIED, 'verify', $comment ?? 'Pemeriksaan berhasil diverifikasi oleh Admin.');
            ActivityLogService::log('memverifikasi', 'Pemeriksaan', "Admin memverifikasi pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Admin submits to Super Admin for approval (ADMIN_VERIFIED / ADMIN_REVISION_REQUIRED -> PENDING_SUPER_ADMIN_APPROVAL)
     */
    public function submitForSuperAdminApproval(Inspection $inspection, User $user, ?string $comment = null): Inspection
    {
        if (!in_array($inspection->status, [InspectionStatus::ADMIN_VERIFIED, InspectionStatus::ADMIN_REVISION_REQUIRED])) {
            throw new Exception("Hanya pemeriksaan yang terverifikasi yang dapat diajukan ke Super Admin.");
        }

        return DB::transaction(function () use ($inspection, $user, $comment) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL;
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, 'submit_approval', $comment ?? 'Diajukan ke Super Admin untuk persetujuan akhir.');
            ActivityLogService::log('mengajukan persetujuan', 'Pemeriksaan', "Mengajukan persetujuan akhir {$inspection->reference_number} ke Super Admin", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Super Admin returns to Admin (PENDING_SUPER_ADMIN_APPROVAL -> ADMIN_REVISION_REQUIRED)
     */
    public function returnToAdmin(Inspection $inspection, User $user, string $reason): Inspection
    {
        if ($inspection->status !== InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL) {
            throw new Exception("Hanya pemeriksaan yang menunggu persetujuan Super Admin yang dapat dikembalikan.");
        }

        if (empty(trim($reason))) {
            throw new Exception("Alasan pengembalian ke Admin wajib diisi.");
        }

        return DB::transaction(function () use ($inspection, $user, $reason) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::ADMIN_REVISION_REQUIRED;
            $inspection->save();

            InspectionNote::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'note_type' => NoteType::SUPER_ADMIN_REVISION,
                'note' => $reason,
                'is_internal' => false,
            ]);

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::ADMIN_REVISION_REQUIRED, 'return_to_admin', $reason);
            ActivityLogService::log('mengembalikan', 'Pemeriksaan', "Super Admin mengembalikan pemeriksaan {$inspection->reference_number} ke Admin: {$reason}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Super Admin approves inspection (PENDING_SUPER_ADMIN_APPROVAL -> APPROVED)
     */
    public function approveBySuperAdmin(Inspection $inspection, User $user, ?string $comment = null): Inspection
    {
        if ($inspection->status !== InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL) {
            throw new Exception("Pemeriksaan tidak dalam status menunggu persetujuan Super Admin.");
        }

        return DB::transaction(function () use ($inspection, $user, $comment) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::APPROVED;
            $inspection->approved_at = now();
            $inspection->completed_at = now();
            $inspection->save();

            InspectionApproval::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'approval_level' => ApprovalLevel::SUPER_ADMIN,
                'decision' => ApprovalDecision::APPROVED,
                'comment' => $comment ?? 'Pemeriksaan disetujui secara final.',
                'signature_snapshot' => $user->signature_path,
                'action_at' => now(),
            ]);

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::APPROVED, 'approve', $comment ?? 'Disetujui secara final oleh Super Admin.');
            ActivityLogService::log('menyetujui', 'Pemeriksaan', "Super Admin menyetujui pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Super Admin locks inspection (APPROVED -> LOCKED)
     */
    public function lockInspection(Inspection $inspection, User $user): Inspection
    {
        if ($inspection->status !== InspectionStatus::APPROVED) {
            throw new Exception("Hanya pemeriksaan yang sudah disetujui (APPROVED) yang dapat dikunci.");
        }

        return DB::transaction(function () use ($inspection, $user) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::LOCKED;
            $inspection->locked_at = now();
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::LOCKED, 'lock', 'Data pemeriksaan dikunci resmi untuk kepatuhan audit.');
            ActivityLogService::log('mengunci', 'Pemeriksaan', "Mengunci data pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Super Admin unlocks inspection with mandatory reason (LOCKED -> ADMIN_REVISION_REQUIRED)
     */
    public function unlockInspection(Inspection $inspection, User $user, string $reason): Inspection
    {
        if ($inspection->status !== InspectionStatus::LOCKED) {
            throw new Exception("Hanya pemeriksaan yang terkunci (LOCKED) yang dapat dibuka kuncinya.");
        }

        if (empty(trim($reason))) {
            throw new Exception("Alasan membuka kunci wajib diisi.");
        }

        return DB::transaction(function () use ($inspection, $user, $reason) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::ADMIN_REVISION_REQUIRED;
            $inspection->locked_at = null;
            $inspection->save();

            InspectionNote::create([
                'inspection_id' => $inspection->id,
                'user_id' => $user->id,
                'note_type' => NoteType::UNLOCK,
                'note' => "Buka kunci: {$reason}",
                'is_internal' => true,
            ]);

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::ADMIN_REVISION_REQUIRED, 'unlock', $reason);
            ActivityLogService::log('membuka kunci', 'Pemeriksaan', "Super Admin membuka kunci pemeriksaan {$inspection->reference_number}: {$reason}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Archive inspection (LOCKED -> ARCHIVED)
     */
    public function archiveInspection(Inspection $inspection, User $user): Inspection
    {
        if ($inspection->status !== InspectionStatus::LOCKED) {
            throw new Exception("Hanya pemeriksaan terkunci yang dapat diarsipkan.");
        }

        return DB::transaction(function () use ($inspection, $user) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::ARCHIVED;
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::ARCHIVED, 'archive', 'Pemeriksaan dipindahkan ke arsip.');
            ActivityLogService::log('mengarsipkan', 'Pemeriksaan', "Mengarsipkan pemeriksaan {$inspection->reference_number}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    /**
     * Cancel inspection (DRAFT / ASSIGNED -> CANCELLED)
     */
    public function cancelInspection(Inspection $inspection, User $user, string $reason): Inspection
    {
        if (!in_array($inspection->status, [InspectionStatus::DRAFT, InspectionStatus::ASSIGNED])) {
            throw new Exception("Hanya pemeriksaan berstatus Draft atau Ditugaskan yang dapat dibatalkan.");
        }

        return DB::transaction(function () use ($inspection, $user, $reason) {
            $fromStatus = $inspection->status;
            $inspection->status = InspectionStatus::CANCELLED;
            $inspection->save();

            $this->logTransition($inspection, $user, $fromStatus, InspectionStatus::CANCELLED, 'cancel', $reason);
            ActivityLogService::log('membatalkan pemeriksaan', 'Pemeriksaan', "Membatalkan pemeriksaan {$inspection->reference_number}: {$reason}", $inspection, null, null, $user->id);

            return $inspection;
        });
    }

    protected function logTransition(
        Inspection $inspection,
        User $user,
        ?InspectionStatus $from,
        InspectionStatus $to,
        string $action,
        ?string $reason = null,
        ?array $metadata = null
    ): InspectionStatusHistory {
        return InspectionStatusHistory::create([
            'inspection_id' => $inspection->id,
            'changed_by' => $user->id,
            'from_status' => $from,
            'to_status' => $to,
            'action' => $action,
            'reason' => $reason,
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
