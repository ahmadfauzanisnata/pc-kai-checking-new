<?php

namespace App\Http\Controllers\Web\Admin;

use App\Enums\DeviceStatus;
use App\Enums\InspectionPeriodStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inspection\BatchAssignInspectionRequest;
use App\Models\Device;
use App\Models\InspectionPeriod;
use App\Models\Unit;
use App\Models\User;
use App\Services\InspectionService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    protected InspectionService $inspectionService;

    public function __construct(InspectionService $inspectionService)
    {
        $this->inspectionService = $inspectionService;
    }

    public function create(Request $request): View
    {
        $user = Auth::user();

        // 1. Periods (ACTIVE periods)
        $periodsQuery = InspectionPeriod::where('status', InspectionPeriodStatus::ACTIVE);
        if ($user->isAdmin()) {
            $periodsQuery->where('business_area_id', $user->business_area_id);
        }
        $periods = $periodsQuery->orderBy('start_date', 'desc')->get();

        // 2. Inspectors (users with role inspector in business area or active)
        $inspectorsQuery = User::whereHas('role', function ($q) {
            $q->where('name', 'inspector');
        })->where('is_active', true);

        if ($user->isAdmin()) {
            $inspectorsQuery->where(function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id)
                    ->orWhereNull('business_area_id');
            });
        }
        $inspectors = $inspectorsQuery->orderBy('name')->get();

        // 3. Units for device filtering
        $unitsQuery = Unit::where('is_active', true);
        if ($user->isAdmin()) {
            $unitsQuery->where('business_area_id', $user->business_area_id);
        }
        $units = $unitsQuery->get();

        // 4. Devices query with filters
        $devicesQuery = Device::with(['employee.unit'])
            ->whereNotNull('employee_id')
            ->where('device_status', DeviceStatus::ACTIVE);

        if ($user->isAdmin()) {
            $devicesQuery->whereHas('employee.unit', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        }

        if ($request->filled('filter_unit_id')) {
            $devicesQuery->whereHas('employee', function ($q) use ($request) {
                $q->where('unit_id', $request->input('filter_unit_id'));
            });
        }

        if ($request->filled('filter_device_type')) {
            $devicesQuery->where('device_type', $request->input('filter_device_type'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $devicesQuery->where(function ($q) use ($search) {
                $q->where('asset_number', 'like', "%{$search}%")
                    ->orWhere('hostname', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($eq) use ($search) {
                        $eq->where('name', 'like', "%{$search}%")
                            ->orWhere('nipp', 'like', "%{$search}%");
                    });
            });
        }

        // If a period is selected, filter out devices already assigned in this period
        $selectedPeriodId = $request->input('inspection_period_id') ?? $periods->first()?->id;
        if ($selectedPeriodId) {
            $devicesQuery->whereDoesntHave('inspections', function ($q) use ($selectedPeriodId) {
                $q->where('inspection_period_id', $selectedPeriodId);
            });
        }

        $devices = $devicesQuery->orderBy('asset_number')->get();

        return view('admin.assignments.create', compact('periods', 'inspectors', 'units', 'devices', 'selectedPeriodId'));
    }

    public function store(BatchAssignInspectionRequest $request): RedirectResponse
    {
        $user = Auth::user();

        try {
            $createdInspections = $this->inspectionService->batchAssign(
                periodId: (int) $request->input('inspection_period_id'),
                inspectorId: (int) $request->input('inspector_id'),
                documentDate: $request->input('document_date'),
                inspectionDate: $request->input('inspection_date'),
                deviceIds: $request->input('device_ids'),
                assignedByUser: $user
            );

            $count = count($createdInspections);
            return redirect()->route('admin.verifications.index')->with(
                'success',
                "Berhasil membuat {$count} penugasan pemeriksaan dengan nomor referensi otomatis."
            );
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
