<?php

namespace App\Http\Controllers\Web;

use App\Enums\InspectionStatus;
use App\Http\Controllers\Controller;
use App\Models\BusinessArea;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Services\ReportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Inspection::with(['employee.unit.businessArea', 'device', 'inspector', 'inspectionPeriod', 'checklist']);

        // Scoping
        if ($user->isAdmin()) {
            $query->whereHas('inspectionPeriod', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        } elseif ($user->isInspector()) {
            $query->where('inspector_id', $user->id);
        }

        // Filters
        if ($request->filled('business_area_id') && $user->isSuperAdmin()) {
            $query->whereHas('inspectionPeriod', function ($q) use ($request) {
                $q->where('business_area_id', $request->input('business_area_id'));
            });
        }

        if ($request->filled('period_id')) {
            $query->where('inspection_period_id', $request->input('period_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('document_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('document_date', '<=', $request->input('end_date'));
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

        $inspections = $query->orderBy('document_date', 'desc')->paginate(15)->withQueryString();

        $businessAreas = $user->isSuperAdmin() ? BusinessArea::where('is_active', true)->get() : collect();
        $periodsQuery = InspectionPeriod::orderBy('start_date', 'desc');
        if ($user->isAdmin()) {
            $periodsQuery->where('business_area_id', $user->business_area_id);
        }
        $periods = $periodsQuery->get();

        return view('reports.index', compact('inspections', 'businessAreas', 'periods'));
    }

    public function preview(Inspection $inspection): View
    {
        $this->authorizeAccess($inspection);

        $data = $this->reportService->prepareInspectionReportData($inspection);

        return view('reports.preview', $data);
    }

    public function downloadPdf(Inspection $inspection): Response
    {
        $this->authorizeAccess($inspection);

        $pdf = $this->reportService->generateInspectionPdf($inspection);
        $filename = 'Pemeriksaan_' . str_replace('/', '_', $inspection->reference_number) . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download consolidated PDF recap for a specific inspection period.
     */
    public function downloadPeriodPdf(InspectionPeriod $period): Response
    {
        $user = Auth::user();
        if ($user->isAdmin() && $period->business_area_id !== $user->business_area_id) {
            abort(403);
        }

        $pdf = $this->reportService->generatePeriodRecapPdf($period);
        $filename = 'Rekap_Pemeriksaan_' . Str::slug($period->name, '_') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download ZIP containing all individual PDFs and the recap PDF for an inspection period.
     */
    public function downloadPeriodZip(InspectionPeriod $period): BinaryFileResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $period->business_area_id !== $user->business_area_id) {
            abort(403);
        }

        $zipPath = $this->reportService->generatePeriodZip($period);

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    /**
     * Download consolidated PDF recap based on current query filters.
     */
    public function downloadRecapPdf(Request $request): Response
    {
        $query = $this->buildFilteredQuery($request);

        $meta = [];
        if ($request->filled('period_id')) {
            $period = InspectionPeriod::find($request->input('period_id'));
            if ($period) {
                $meta['periodName'] = $period->name;
                $meta['businessAreaName'] = $period->businessArea?->name ?? 'Semua Wilayah';
                $meta['dateRange'] = $period->start_date && $period->end_date
                    ? $period->start_date->format('d/m/Y') . ' - ' . $period->end_date->format('d/m/Y')
                    : 'Periode ' . $period->name;
            }
        } elseif ($request->filled('status')) {
            $status = $request->input('status');
            $meta['periodName'] = "Pemeriksaan Status {$status}";
        }

        $pdf = $this->reportService->generateRecapPdf($query, $meta);
        $filename = 'Laporan_Rekapitulasi_Pemeriksaan_' . now()->format('Ymd_His') . '.pdf';

        return $pdf->stream($filename);
    }

    /**
     * Download ZIP containing all individual PDFs based on current query filters.
     */
    public function downloadZip(Request $request): BinaryFileResponse
    {
        $query = $this->buildFilteredQuery($request);

        $baseName = 'Arsip_Pemeriksaan';
        if ($request->filled('period_id')) {
            $period = InspectionPeriod::find($request->input('period_id'));
            if ($period) {
                $baseName = 'Arsip_' . Str::slug($period->name, '_');
            }
        } elseif ($request->filled('status')) {
            $baseName = 'Arsip_Pemeriksaan_' . $request->input('status');
        }

        $zipPath = $this->reportService->generateInspectionsZip($query, $baseName);

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $query = $this->buildFilteredQuery($request);

        return $this->reportService->exportInspectionsExcel($query);
    }

    public function exportPeriod(InspectionPeriod $period): BinaryFileResponse
    {
        $user = Auth::user();
        if ($user->isAdmin() && $period->business_area_id !== $user->business_area_id) {
            abort(403);
        }

        return $this->reportService->exportPeriodExcel($period);
    }

    protected function buildFilteredQuery(Request $request): Builder
    {
        $user = Auth::user();
        $query = Inspection::query();

        if ($user->isAdmin()) {
            $query->whereHas('inspectionPeriod', function ($q) use ($user) {
                $q->where('business_area_id', $user->business_area_id);
            });
        } elseif ($user->isInspector()) {
            $query->where('inspector_id', $user->id);
        }

        if ($request->filled('business_area_id') && $user->isSuperAdmin()) {
            $query->whereHas('inspectionPeriod', function ($q) use ($request) {
                $q->where('business_area_id', $request->input('business_area_id'));
            });
        }

        if ($request->filled('period_id')) {
            $query->where('inspection_period_id', $request->input('period_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('document_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('document_date', '<=', $request->input('end_date'));
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

        return $query;
    }

    protected function authorizeAccess(Inspection $inspection): void
    {
        $user = Auth::user();
        if ($user->isInspector() && $inspection->inspector_id !== $user->id) {
            abort(403);
        }
        if ($user->isAdmin() && $inspection->inspectionPeriod?->business_area_id !== $user->business_area_id) {
            abort(403);
        }
    }
}
