<?php

namespace App\Http\Controllers\Web\SuperAdmin;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inspection\ApprovalActionRequest;
use App\Http\Requests\Inspection\UnlockInspectionRequest;
use App\Models\BusinessArea;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\Unit;
use App\Models\User;
use App\Services\InspectionStatusService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SuperAdminInspectionController extends Controller
{
    protected InspectionStatusService $statusService;

    public function __construct(InspectionStatusService $statusService)
    {
        $this->statusService = $statusService;
    }

    public function index(Request $request): View
    {
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('business_area_id')) {
            $query->whereHas('inspectionPeriod', function ($q) use ($request) {
                $q->where('business_area_id', $request->input('business_area_id'));
            });
        }

        if ($request->filled('period_id')) {
            $query->where('inspection_period_id', $request->input('period_id'));
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
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('nipp', 'like', "%{$search}%");
                    });
            });
        }

        $inspections = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        $businessAreas = BusinessArea::where('is_active', true)->get();
        $periods = InspectionPeriod::orderBy('start_date', 'desc')->get();

        return view('super-admin.inspections.index', compact('inspections', 'businessAreas', 'periods'));
    }

    public function pending(Request $request): View
    {
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist'])
            ->where('status', InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference_number', 'like', "%{$search}%")
                    ->orWhereHas('device', function ($dq) use ($search) {
                        $dq->where('asset_number', 'like', "%{$search}%");
                    });
            });
        }

        $inspections = $query->orderBy('submitted_at', 'asc')->paginate(10)->withQueryString();

        return view('super-admin.inspections.pending-approval', compact('inspections'));
    }

    public function approved(Request $request): View
    {
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist'])
            ->where('status', InspectionStatus::APPROVED);

        $inspections = $query->orderBy('approved_at', 'desc')->paginate(10)->withQueryString();

        return view('super-admin.inspections.approved', compact('inspections'));
    }

    public function locked(Request $request): View
    {
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist'])
            ->where('status', InspectionStatus::LOCKED);

        $inspections = $query->orderBy('locked_at', 'desc')->paginate(10)->withQueryString();

        return view('super-admin.inspections.locked', compact('inspections'));
    }

    public function archived(Request $request): View
    {
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist'])
            ->where('status', InspectionStatus::ARCHIVED);

        $inspections = $query->orderBy('updated_at', 'desc')->paginate(10)->withQueryString();

        return view('super-admin.inspections.archived', compact('inspections'));
    }

    public function showApproval(Inspection $inspection): View
    {
        $inspection->load([
            'employee.unit.businessArea',
            'device',
            'inspector',
            'inspectionPeriod.businessArea',
            'checklist',
            'attachments.uploadedBy',
            'notes.user',
            'approvals.user.role',
            'statusHistories.changedBy.role',
        ]);

        return view('super-admin.inspections.approval-detail', compact('inspection'));
    }

    public function handleApproval(ApprovalActionRequest $request, Inspection $inspection): RedirectResponse
    {
        $action = $request->input('action');
        $user = Auth::user();

        try {
            if ($action === 'approve') {
                $this->statusService->approveBySuperAdmin($inspection, $user, $request->input('comment'));
                return redirect()->route('super-admin.inspections.approved')
                    ->with('success', "Pemeriksaan {$inspection->reference_number} telah disetujui secara resmi.");
            } else {
                $this->statusService->returnToAdmin($inspection, $user, $request->input('reason'));
                return redirect()->route('super-admin.inspections.pending')
                    ->with('warning', "Pemeriksaan {$inspection->reference_number} telah dikembalikan ke Admin untuk perbaikan.");
            }
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function lock(Inspection $inspection): RedirectResponse
    {
        try {
            $this->statusService->lockInspection($inspection, Auth::user());
            return back()->with('success', "Data pemeriksaan {$inspection->reference_number} telah dikunci.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function unlock(UnlockInspectionRequest $request, Inspection $inspection): RedirectResponse
    {
        try {
            $this->statusService->unlockInspection($inspection, Auth::user(), $request->input('reason'));
            return redirect()->route('super-admin.inspections.index')
                ->with('success', "Kunci data pemeriksaan {$inspection->reference_number} berhasil dibuka dengan alasan tercatat.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function archive(Inspection $inspection): RedirectResponse
    {
        try {
            $this->statusService->archiveInspection($inspection, Auth::user());
            return redirect()->route('super-admin.inspections.archived')
                ->with('success', "Pemeriksaan {$inspection->reference_number} telah diarsipkan.");
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
