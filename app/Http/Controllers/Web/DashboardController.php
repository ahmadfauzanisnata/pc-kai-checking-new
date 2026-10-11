<?php

namespace App\Http\Controllers\Web;

use App\Enums\InspectionPeriodStatus;
use App\Enums\InspectionStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        $user = Auth::user();

        return match ($user->role?->name) {
            UserRole::SUPER_ADMIN->value => redirect()->route('super-admin.dashboard'),
            UserRole::ADMIN->value => redirect()->route('admin.dashboard'),
            UserRole::INSPECTOR->value => redirect()->route('inspector.dashboard'),
            default => redirect()->route('login'),
        };
    }

    public function superAdmin(): View
    {
        $stats = [
            'total_business_areas' => BusinessArea::count(),
            'total_users' => User::count(),
            'total_devices' => Device::count(),
            'total_employees' => Employee::count(),
            'total_periods' => InspectionPeriod::count(),
            'total_inspections' => Inspection::count(),
            'pending_approval' => Inspection::where('status', InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL)->count(),
            'approved' => Inspection::where('status', InspectionStatus::APPROVED)->count(),
            'locked' => Inspection::where('status', InspectionStatus::LOCKED)->count(),
            'in_progress' => Inspection::whereIn('status', [
                InspectionStatus::ASSIGNED,
                InspectionStatus::IN_PROGRESS,
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::REVISION_REQUIRED,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::ADMIN_VERIFIED,
                InspectionStatus::ADMIN_REVISION_REQUIRED,
            ])->count(),
        ];

        // Chart Data: Status Breakdown
        $statusCounts = Inspection::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statusLabels = [];
        $statusData = [];
        foreach (InspectionStatus::cases() as $status) {
            if (($statusCounts[$status->value] ?? 0) > 0) {
                $statusLabels[] = $status->label();
                $statusData[] = $statusCounts[$status->value];
            }
        }

        // Chart Data: Business Area Units & Total
        $businessAreas = BusinessArea::withCount('units')->get();
        $baLabels = [];
        $baDevices = [];
        foreach ($businessAreas as $ba) {
            $baLabels[] = $ba->code;
            $baDevices[] = $ba->units_count;
        }

        // Recent Activity Logs
        $recentActivities = ActivityLog::with('user')->latest()->take(8)->get();

        // Inspections Waiting for Super Admin Approval
        $pendingInspections = Inspection::with(['employee.unit', 'device', 'inspector', 'inspectionPeriod.businessArea'])
            ->where('status', InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL)
            ->latest('submitted_at')
            ->take(5)
            ->get();

        return view('super-admin.dashboard', compact(
            'stats',
            'statusLabels',
            'statusData',
            'baLabels',
            'baDevices',
            'recentActivities',
            'pendingInspections'
        ));
    }

    public function admin(): View
    {
        $user = Auth::user();
        $baId = $user->business_area_id;

        $devicesQuery = Device::query();
        $employeesQuery = Employee::query();
        $periodsQuery = InspectionPeriod::where('status', InspectionPeriodStatus::ACTIVE);
        $inspectionsQuery = Inspection::query();

        if ($baId) {
            $devicesQuery->whereHas('employee.unit', fn($q) => $q->where('business_area_id', $baId));
            $employeesQuery->whereHas('unit', fn($q) => $q->where('business_area_id', $baId));
            $periodsQuery->where('business_area_id', $baId);
            $inspectionsQuery->whereHas('inspectionPeriod', fn($q) => $q->where('business_area_id', $baId));
        }

        $activePeriod = $periodsQuery->latest()->first();

        $stats = [
            'total_devices' => $devicesQuery->count(),
            'total_employees' => $employeesQuery->count(),
            'active_period' => $activePeriod,
            'pending_verifications' => (clone $inspectionsQuery)->whereIn('status', [
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::ADMIN_REVISION_REQUIRED,
            ])->count(),
            'returned_count' => (clone $inspectionsQuery)->where('status', InspectionStatus::ADMIN_REVISION_REQUIRED)->count(),
            'revision_count' => (clone $inspectionsQuery)->where('status', InspectionStatus::REVISION_REQUIRED)->count(),
            'approved_count' => (clone $inspectionsQuery)->whereIn('status', [
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
            ])->count(),
        ];

        // Active period progress
        $periodProgress = null;
        if ($stats['active_period']) {
            $totalInPeriod = Inspection::where('inspection_period_id', $stats['active_period']->id)->count();
            $completedInPeriod = Inspection::where('inspection_period_id', $stats['active_period']->id)
                ->whereIn('status', [InspectionStatus::APPROVED, InspectionStatus::LOCKED, InspectionStatus::ARCHIVED])
                ->count();
            $percentage = $totalInPeriod > 0 ? round(($completedInPeriod / $totalInPeriod) * 100) : 0;
            $periodProgress = [
                'total' => $totalInPeriod,
                'completed' => $completedInPeriod,
                'percentage' => $percentage,
            ];
        }

        // Inspections needing Admin review
        $pendingReviewsQuery = Inspection::with(['employee.unit', 'device', 'inspector']);
        if ($baId) {
            $pendingReviewsQuery->whereHas('inspectionPeriod', fn($q) => $q->where('business_area_id', $baId));
        }
        $pendingReviews = $pendingReviewsQuery
            ->whereIn('status', [
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::ADMIN_REVISION_REQUIRED,
            ])
            ->latest('submitted_at')
            ->take(5)
            ->get();

        // Inspections returned by Super Admin requiring Admin attention
        $returnedInspectionsQuery = Inspection::with(['employee.unit', 'device', 'inspector', 'notes.user']);
        if ($baId) {
            $returnedInspectionsQuery->whereHas('inspectionPeriod', fn($q) => $q->where('business_area_id', $baId));
        }
        $returnedInspections = $returnedInspectionsQuery
            ->where('status', InspectionStatus::ADMIN_REVISION_REQUIRED)
            ->latest('updated_at')
            ->get();

        return view('admin.dashboard', compact('stats', 'periodProgress', 'pendingReviews', 'returnedInspections'));
    }
}
