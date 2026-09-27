<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Models\Inspection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportApiController extends Controller
{
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = Inspection::query();

        if ($user->isAdmin()) {
            $query->whereHas('inspectionPeriod', fn($q) => $q->where('business_area_id', $user->business_area_id));
        } elseif ($user->isInspector()) {
            $query->where('inspector_id', $user->id);
        }

        $total = (clone $query)->count();
        $approved = (clone $query)->where('status', InspectionStatus::APPROVED)->count();
        $locked = (clone $query)->where('status', InspectionStatus::LOCKED)->count();
        $archived = (clone $query)->where('status', InspectionStatus::ARCHIVED)->count();
        $pending = (clone $query)->whereIn('status', [
            InspectionStatus::PENDING_ADMIN_VERIFICATION,
            InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
            InspectionStatus::RESUBMITTED,
        ])->count();

        // Findings count
        $withFindings = (clone $query)->whereHas('checklist', function ($q) {
            $q->where('nda_status', 'BELUM')
                ->orWhere('strong_password_status', 'BELUM')
                ->orWhere('screensaver_lock_status', 'BELUM')
                ->orWhere('cleardesk_status', 'BELUM')
                ->orWhere('prohibited_files_status', 'ADA')
                ->orWhere('antivirus_install_status', 'BELUM')
                ->orWhere('antivirus_update_status', 'BELUM')
                ->orWhere('full_scan_schedule_status', 'BELUM')
                ->orWhere('os_license_status', 'TIDAK')
                ->orWhere('ntp_sync_status', 'TIDAK')
                ->orWhere('pc_label_status', 'TIDAK');
        })->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_inspections' => $total,
                'completed' => $approved + $locked + $archived,
                'pending' => $pending,
                'with_findings' => $withFindings,
                'compliance_rate_percent' => $total > 0 ? round((($total - $withFindings) / $total) * 100, 2) : 100,
            ],
        ]);
    }
}
