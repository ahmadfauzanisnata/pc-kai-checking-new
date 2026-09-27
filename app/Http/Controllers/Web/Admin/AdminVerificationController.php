<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inspection\RequestRevisionRequest;
use App\Http\Requests\Inspection\VerifyInspectionRequest;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\Unit;
use App\Services\InspectionStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminVerificationController extends Controller
{
    protected InspectionStatusService $statusService;

    public function __construct(InspectionStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Inspection::with(['employee.unit', 'device', 'inspector', 'inspectionPeriod', 'checklist']);

        if ($user->isAdmin()) {
            $query->whereHas('inspectionPeriod', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            // Default show pending verifications and resubmitted
            $query->whereIn('status', [
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::REVISION_REQUIRED,
                InspectionStatus::ADMIN_VERIFIED,
                InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
            ]);
        }

        if ($request->filled('period_id')) {
            $query->where('inspection_period_id', $request->input('period_id'));
        }

        if ($request->filled('unit_id')) {
            $query->whereHas('employee', function ($q) use ($request) {
                $q->where('unit_id', $request->input('unit_id'));
            });
        }

        if ($request->filled('has_findings')) {
            if ($request->input('has_findings') === 'yes') {
                $query->whereHas('checklist', function ($q) {
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
                });
            }
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('device', function ($dq) use ($search) {
                        $dq->where('asset_number', 'like', "%{$search}%")
                            ->orWhere('hostname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $inspections = $query->orderBy('submitted_at', 'desc')->paginate(10)->withQueryString();

        $periodsQuery = InspectionPeriod::orderBy('start_date', 'desc');
        if ($user->isAdmin()) {
            $periodsQuery->where('business_area_id', $user->business_area_id);
        }
        $periods = $periodsQuery->get();

        $unitsQuery = Unit::where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->get();

        return view('admin.verifications.index', compact('inspections', 'periods', 'units'));
    }

    public function show(Inspection $inspection): View
    {
        $this->authorizeArea($inspection);

        $inspection->load([
            'employee.unit.businessArea',
            'device',
            'inspector',
            'inspectionPeriod',
            'checklist',
            'attachments.uploadedBy',
            'notes.user',
            'approvals.user',
            'statusHistories.changedBy',
        ]);

        return view('admin.verifications.verify', compact('inspection'));
    }

    public function verify(VerifyInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeArea($inspection);

        try {
            $this->statusService->verifyByAdmin($inspection, Auth::user(), $request->input('comment'));
            return redirect()->route('admin.verifications.index')
                ->with('success', "Pemeriksaan {$inspection->reference_number} berhasil diverifikasi.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function requestRevision(RequestRevisionRequest $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeArea($inspection);

        try {
            $this->statusService->requestRevisionByAdmin(
                $inspection,
                Auth::user(),
                $request->input('reason'),
                $request->input('flagged_items', [])
            );

            return redirect()->route('admin.verifications.index')
                ->with('warning', "Pemeriksaan {$inspection->reference_number} telah dikembalikan kepada Pemeriksa untuk perbaikan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function submitApproval(Request $request, Inspection $inspection): RedirectResponse
    {
        $this->authorizeArea($inspection);

        try {
            $this->statusService->submitForSuperAdminApproval($inspection, Auth::user(), $request->input('comment'));
            return redirect()->route('admin.verifications.index')
                ->with('success', "Pemeriksaan {$inspection->reference_number} telah diajukan ke Super Admin untuk persetujuan akhir.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    protected function authorizeArea(Inspection $inspection): void
    {
        $user = Auth::user();
        if ($user->isAdmin() && $inspection->inspectionPeriod?->business_area_id !== $user->business_area_id) {
            abort(403, 'Anda tidak berwenang memverifikasi data di luar Business Area Anda.');
        }
    }
}
