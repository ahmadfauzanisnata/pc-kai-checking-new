<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardApiController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $data = [
                'total_business_areas' => BusinessArea::count(),
                'total_devices' => Device::count(),
                'total_employees' => Employee::count(),
                'total_inspections' => Inspection::count(),
                'pending_super_admin' => Inspection::where('status', InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL)->count(),
                'approved' => Inspection::where('status', InspectionStatus::APPROVED)->count(),
                'locked' => Inspection::where('status', InspectionStatus::LOCKED)->count(),
            ];
        } elseif ($user->isAdmin()) {
            $baId = $user->business_area_id;
            $data = [
                'total_devices' => Device::where('business_area_id', $baId)->count(),
                'total_employees' => Employee::where('business_area_id', $baId)->count(),
                'pending_verifications' => Inspection::whereHas('inspectionPeriod', function ($q) use ($baId) {
                    $q->where('business_area_id', $baId);
                })->whereIn('status', [InspectionStatus::PENDING_ADMIN_VERIFICATION, InspectionStatus::RESUBMITTED])->count(),
                'revision_required' => Inspection::whereHas('inspectionPeriod', function ($q) use ($baId) {
                    $q->where('business_area_id', $baId);
                })->where('status', InspectionStatus::REVISION_REQUIRED)->count(),
                'approved' => Inspection::whereHas('inspectionPeriod', function ($q) use ($baId) {
                    $q->where('business_area_id', $baId);
                })->whereIn('status', [InspectionStatus::APPROVED, InspectionStatus::LOCKED])->count(),
            ];
        } else {
            $data = [
                'assigned' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::ASSIGNED)->count(),
                'in_progress' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::IN_PROGRESS)->count(),
                'revision_required' => Inspection::where('inspector_id', $user->id)->where('status', InspectionStatus::REVISION_REQUIRED)->count(),
                'submitted' => Inspection::where('inspector_id', $user->id)->whereIn('status', [
                    InspectionStatus::PENDING_ADMIN_VERIFICATION,
                    InspectionStatus::RESUBMITTED,
                    InspectionStatus::ADMIN_VERIFIED,
                    InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                ])->count(),
                'completed' => Inspection::where('inspector_id', $user->id)->whereIn('status', [
                    InspectionStatus::APPROVED,
                    InspectionStatus::LOCKED,
                    InspectionStatus::ARCHIVED,
                ])->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'role' => $user->role?->name,
            'data' => $data,
        ]);
    }
}
