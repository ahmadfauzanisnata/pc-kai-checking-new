<?php

namespace App\Services;

use App\Enums\InspectionStatus;
use App\Models\Device;
use App\Models\Inspection;
use App\Models\InspectionChecklist;
use App\Models\InspectionPeriod;
use App\Models\InspectionStatusHistory;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class InspectionService
{
    /**
     * Create batch assignments for a set of devices
     */
    public function batchAssign(
        int $periodId,
        int $inspectorId,
        string $documentDate,
        ?string $inspectionDate,
        array $deviceIds,
        User $assignedByUser
    ): array {
        $period = InspectionPeriod::findOrFail($periodId);
        $inspector = User::findOrFail($inspectorId);

        if (!$inspector->isInspector()) {
            throw new Exception("Pengguna yang dipilih bukan seorang Pemeriksa.");
        }

        // Scope check for Admin
        if ($assignedByUser->isAdmin() && $assignedByUser->business_area_id !== $period->business_area_id) {
            throw new Exception("Anda tidak berwenang membuat penugasan di luar Business Area Anda.");
        }

        return DB::transaction(function () use ($period, $inspector, $documentDate, $inspectionDate, $deviceIds, $assignedByUser) {
            $createdInspections = [];

            foreach ($deviceIds as $deviceId) {
                $device = Device::with('employee')->findOrFail($deviceId);

                if (!$device->employee_id) {
                    throw new Exception("Perangkat {$device->asset_number} ({$device->hostname}) belum memiliki pegawai penanggung jawab.");
                }

                // Check duplicate in period
                $alreadyExists = Inspection::where('inspection_period_id', $period->id)
                    ->where('device_id', $device->id)
                    ->exists();

                if ($alreadyExists) {
                    throw new Exception("Perangkat {$device->asset_number} sudah memiliki penugasan pada periode ini.");
                }

                $referenceNumber = ReferenceNumberService::generate($documentDate);

                $inspection = Inspection::create([
                    'public_id' => (string) Str::uuid(),
                    'inspection_period_id' => $period->id,
                    'employee_id' => $device->employee_id,
                    'device_id' => $device->id,
                    'inspector_id' => $inspector->id,
                    'reference_number' => $referenceNumber,
                    'document_date' => $documentDate,
                    'inspection_date' => $inspectionDate,
                    'status' => InspectionStatus::ASSIGNED,
                ]);

                // Create initial empty checklist record
                InspectionChecklist::create([
                    'inspection_id' => $inspection->id,
                ]);

                // Initial status history
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $assignedByUser->id,
                    'from_status' => null,
                    'to_status' => InspectionStatus::ASSIGNED,
                    'action' => 'assign',
                    'reason' => "Penugasan baru pada periode {$period->name}",
                    'created_at' => now(),
                ]);

                ActivityLogService::log(
                    'membuat penugasan',
                    'Penugasan',
                    "Membuat penugasan {$inspection->reference_number} untuk {$device->asset_number} kepada {$inspector->name}",
                    $inspection,
                    null,
                    null,
                    $assignedByUser->id
                );

                $createdInspections[] = $inspection;
            }

            return $createdInspections;
        });
    }

    /**
     * Save draft checklist
     */
    public function saveDraft(Inspection $inspection, array $data, User $user): Inspection
    {
        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            throw new Exception("Anda tidak memiliki akses ke pemeriksaan ini.");
        }

        if (!in_array($inspection->status, [InspectionStatus::ASSIGNED, InspectionStatus::IN_PROGRESS, InspectionStatus::REVISION_REQUIRED])) {
            throw new Exception("Pemeriksaan dalam status saat ini tidak dapat diedit sebagai draft.");
        }

        return DB::transaction(function () use ($inspection, $data, $user) {
            // If still ASSIGNED, move to IN_PROGRESS upon saving draft
            if ($inspection->status === InspectionStatus::ASSIGNED) {
                $inspection->status = InspectionStatus::IN_PROGRESS;
                $inspection->inspection_date = $data['inspection_date'] ?? now()->toDateString();
                $inspection->save();

                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $user->id,
                    'from_status' => InspectionStatus::ASSIGNED,
                    'to_status' => InspectionStatus::IN_PROGRESS,
                    'action' => 'start',
                    'reason' => 'Pemeriksa mulai mengisi checklist dan menyimpan draft.',
                    'created_at' => now(),
                ]);
            } else {
                if (!empty($data['inspection_date'])) {
                    $inspection->inspection_date = $data['inspection_date'];
                    $inspection->save();
                }
            }

            if (!empty($data['general_notes'])) {
                $inspection->general_notes = $data['general_notes'];
                $inspection->save();
            }

            $checklist = $inspection->checklist ?? new InspectionChecklist(['inspection_id' => $inspection->id]);
            $checklist->fill([
                'nda_status' => $data['nda_status'] ?? null,
                'strong_password_status' => $data['strong_password_status'] ?? null,
                'screensaver_lock_status' => $data['screensaver_lock_status'] ?? null,
                'special_access' => $data['special_access'] ?? null,
                'cleardesk_status' => $data['cleardesk_status'] ?? null,
                'prohibited_files_status' => $data['prohibited_files_status'] ?? null,
                'antivirus_install_status' => $data['antivirus_install_status'] ?? null,
                'antivirus_update_status' => $data['antivirus_update_status'] ?? null,
                'full_scan_schedule_status' => $data['full_scan_schedule_status'] ?? null,
                'os_license_status' => $data['os_license_status'] ?? null,
                'ntp_sync_status' => $data['ntp_sync_status'] ?? null,
                'pc_label_status' => $data['pc_label_status'] ?? null,
                'finding_summary' => $data['finding_summary'] ?? null,
            ]);
            $checklist->save();

            ActivityLogService::log(
                'menyimpan checklist',
                'Pemeriksaan',
                "Menyimpan draft checklist pemeriksaan {$inspection->reference_number}",
                $inspection,
                null,
                null,
                $user->id
            );

            return $inspection;
        });
    }
}
