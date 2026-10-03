<?php

namespace App\Services;

use App\Enums\ApprovalDecision;
use App\Enums\InspectionStatus;
use App\Enums\UserRole;
use App\Exports\InspectionExport;
use App\Exports\InspectionPeriodExport;
use App\Models\FormConfiguration;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\Unit;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportService
{
    /**
     * Helper to load KAI logo as base64 string for PDF rendering.
     */
    public function getLogoBase64(): ?string
    {
        $formConfig = FormConfiguration::first();
        if (!$formConfig || !$formConfig->logo_path) {
            return null;
        }

        $dir = dirname($formConfig->logo_path);
        $base = pathinfo($formConfig->logo_path, PATHINFO_FILENAME);
        $ext = pathinfo($formConfig->logo_path, PATHINFO_EXTENSION);
        $smallPath = $dir . '/' . $base . '_small.' . $ext;

        $logoFile = storage_path('app/public/' . $smallPath);
        if (!file_exists($logoFile)) {
            $logoFile = storage_path('app/public/' . $formConfig->logo_path);
        }

        if (file_exists($logoFile)) {
            $mime = mime_content_type($logoFile) ?: 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
        }

        return null;
    }

    /**
     * Calculate summary statistics for recap report.
     */
    public function calculateRecapStats($inspections): array
    {
        $total = $inspections->count();
        $pcCount = $inspections->filter(fn($i) => ($i->device?->device_type?->value ?? '') === 'PC')->count();
        $laptopCount = $inspections->filter(fn($i) => ($i->device?->device_type?->value ?? '') === 'LAPTOP')->count();

        $compliantCount = $inspections->filter(function ($i) {
            return $i->checklist && $i->checklist->getNegativeFindingsCount() === 0;
        })->count();

        $findingsCount = $total - $compliantCount;
        $complianceRate = $total > 0 ? round(($compliantCount / $total) * 100, 1) : 100;

        return [
            'total' => $total,
            'pc_count' => $pcCount,
            'laptop_count' => $laptopCount,
            'compliant_count' => $compliantCount,
            'findings_count' => $findingsCount,
            'compliance_rate' => $complianceRate,
        ];
    }

    /**
     * Prepare data needed for both PDF report and HTML preview.
     */
    public function prepareInspectionReportData(Inspection $inspection): array
    {
        $inspection->load([
            'employee.unit.businessArea',
            'device',
            'inspector',
            'inspectionPeriod.businessArea',
            'checklist',
            'approvals.user.role',
            'notes.user',
        ]);

        $isDraft = !in_array($inspection->status, [
            InspectionStatus::APPROVED,
            InspectionStatus::LOCKED,
            InspectionStatus::ARCHIVED,
        ]);

        $formConfig = FormConfiguration::first();
        $logoBase64 = $this->getLogoBase64();

        $approverApproval = $inspection->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->last();

        $approverUser = $approverApproval?->user;
        $approverName = $approverUser?->name;
        $approverNipp = $approverUser?->nipp;

        $approverSig = null;
        $isManualApproval = ($approverApproval?->signature_type === 'manual');

        if (! $isManualApproval) {
            $approverSigPath = $approverApproval?->signature_snapshot ?: $approverUser?->signature_path;
            if ($approverSigPath && Storage::disk('public')->exists($approverSigPath)) {
                $content = Storage::disk('public')->get($approverSigPath);
                $mime = Storage::disk('public')->mimeType($approverSigPath) ?: 'image/png';
                $approverSig = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        $inspectorSig = null;
        if ($inspection->inspector && $inspection->inspector->signature_path && Storage::disk('public')->exists($inspection->inspector->signature_path)) {
            $content = Storage::disk('public')->get($inspection->inspector->signature_path);
            $mime = Storage::disk('public')->mimeType($inspection->inspector->signature_path) ?: 'image/png';
            $inspectorSig = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        $employeeSig = null;
        $employeeSigPath = $inspection->employee_signature_path ?: $inspection->employee?->signature_path;
        if ($employeeSigPath && Storage::disk('public')->exists($employeeSigPath)) {
            $content = Storage::disk('public')->get($employeeSigPath);
            $mime = Storage::disk('public')->mimeType($employeeSigPath) ?: 'image/png';
            $employeeSig = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        return [
            'inspection' => $inspection,
            'isDraft' => $isDraft,
            'formConfig' => $formConfig,
            'logoBase64' => $logoBase64,
            'approverName' => $approverName,
            'approverNipp' => $approverNipp,
            'approverSig' => $approverSig,
            'isManualApproval' => $isManualApproval,
            'inspectorSig' => $inspectorSig,
            'employeeSig' => $employeeSig,
        ];
    }

    /**
     * Generate landscape DomPDF for single inspection according to KAI standard FR.SM/TI/017.002/10-2020.
     */
    public function generateInspectionPdf(Inspection $inspection): DomPdfInstance
    {
        $data = $this->prepareInspectionReportData($inspection);

        return Pdf::loadView('reports.pdf.inspection', $data)->setPaper('a4', 'landscape');
    }

    /**
     * Prepare data needed for multi-inspection recap PDF (both consolidated and per-unit)
     * according to official KAI FR.SM/TI/017.002/10-2020 layout with automatic signatures.
     */
    public function prepareRecapReportData($inspections, ?InspectionPeriod $period = null, ?string $unitName = null, array $meta = []): array
    {
        if ($inspections instanceof Builder) {
            $inspections = $inspections->with([
                'employee.unit.businessArea',
                'device',
                'inspector',
                'inspectionPeriod.businessArea',
                'checklist',
                'approvals.user.role',
                'notes.user',
            ])->orderBy('document_date')->get();
        } else {
            if ($inspections instanceof \Illuminate\Support\Collection && !($inspections instanceof \Illuminate\Database\Eloquent\Collection)) {
                $inspections = new \Illuminate\Database\Eloquent\Collection($inspections);
            }
            $inspections->loadMissing([
                'employee.unit.businessArea',
                'device',
                'inspector',
                'inspectionPeriod.businessArea',
                'checklist',
                'approvals.user.role',
                'notes.user',
            ]);
        }

        $formConfig = FormConfiguration::first();
        $logoBase64 = $this->getLogoBase64();

        // 1. Process each inspection: prepare base64 signatures for inspector and employee
        foreach ($inspections as $insp) {
            $inspectorSig = null;
            if ($insp->inspector && $insp->inspector->signature_path && Storage::disk('public')->exists($insp->inspector->signature_path)) {
                $content = Storage::disk('public')->get($insp->inspector->signature_path);
                $mime = Storage::disk('public')->mimeType($insp->inspector->signature_path) ?: 'image/png';
                $inspectorSig = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
            $insp->inspector_sig_base64 = $inspectorSig;

            $employeeSig = null;
            $employeeSigPath = $insp->employee_signature_path ?: $insp->employee?->signature_path;
            if ($employeeSigPath && Storage::disk('public')->exists($employeeSigPath)) {
                $content = Storage::disk('public')->get($employeeSigPath);
                $mime = Storage::disk('public')->mimeType($employeeSigPath) ?: 'image/png';
                $employeeSig = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
            $insp->employee_sig_base64 = $employeeSig;
        }

        // 2. Determine approver and approver signature for "Mengetahui"
        $approverUser = null;
        $approverApproval = null;

        // Try to find SuperAdmin approved approval first
        foreach ($inspections as $insp) {
            $appr = $insp->approvals
                ->where('decision', ApprovalDecision::APPROVED)
                ->filter(fn($a) => $a->user?->isSuperAdmin())
                ->last();
            if ($appr) {
                $approverApproval = $appr;
                $approverUser = $appr->user;
                break;
            }
        }

        // Otherwise find any approved approval
        if (!$approverUser) {
            foreach ($inspections as $insp) {
                $appr = $insp->approvals
                    ->where('decision', ApprovalDecision::APPROVED)
                    ->last();
                if ($appr) {
                    $approverApproval = $appr;
                    $approverUser = $appr->user;
                    break;
                }
            }
        }

        // Fallback to super admin in database if none in approvals
        if (!$approverUser) {
            $approverUser = User::whereHas('role', fn($q) => $q->where('name', UserRole::SUPER_ADMIN->value))->first();
        }

        $approverName = $approverUser?->name;
        $approverNipp = $approverUser?->nipp;
        $approverSig = null;
        $isManualApproval = ($approverApproval?->signature_type === 'manual');

        if (!$isManualApproval && $approverUser) {
            $approverSigPath = $approverApproval?->signature_snapshot ?: $approverUser->signature_path;
            if ($approverSigPath && Storage::disk('public')->exists($approverSigPath)) {
                $content = Storage::disk('public')->get($approverSigPath);
                $mime = Storage::disk('public')->mimeType($approverSigPath) ?: 'image/png';
                $approverSig = 'data:' . $mime . ';base64,' . base64_encode($content);
            }
        }

        // 3. Metadata details
        $periodName = $period?->name ?? ($meta['periodName'] ?? 'Rekapitulasi Pemeriksaan');
        $businessAreaName = $period?->businessArea?->name
            ?? ($meta['businessAreaName']
                ?? ($inspections->first()?->employee?->unit?->businessArea?->name ?? 'Daop 2 Bandung'));

        if ($period && $period->start_date && $period->end_date) {
            $dateRange = $period->start_date->translatedFormat('d F Y') . ' s/d ' . $period->end_date->translatedFormat('d F Y');
        } else {
            $dateRange = $meta['dateRange'] ?? now()->translatedFormat('F Y');
        }

        $documentDate = $period?->end_date ?? ($inspections->first()?->document_date ?? now());
        $refNumber = $meta['refNumber'] ?? ($period ? 'REKAP/' . Str::upper(Str::slug($period->name, '-')) : ($inspections->first()?->reference_number ?? 'REKAP/' . now()->format('Ymd')));

        // 4. Catatan summary
        $notesList = [];
        foreach ($inspections as $insp) {
            if (!empty($insp->checklist?->finding_summary) && !in_array(trim($insp->checklist->finding_summary), ['Sesuai standar', '-'])) {
                $notesList[] = ($insp->employee?->name ?? 'Perangkat') . ': ' . $insp->checklist->finding_summary;
            } elseif ($insp->notes && $insp->notes->count() > 0) {
                $notesList[] = ($insp->employee?->name ?? 'Perangkat') . ': ' . $insp->notes->pluck('content')->implode('; ');
            }
        }
        $notesSummary = !empty($notesList) ? implode('; ', array_slice($notesList, 0, 5)) : 'Pemeriksaan telah dilaksanakan sesuai standar.';

        // 5. Draft watermark flag
        $isDraft = false;
        if ($period) {
            $isDraft = !in_array($period->status->value ?? $period->status, ['CLOSED', 'LOCKED']);
        } elseif ($inspections->count() > 0) {
            $isDraft = $inspections->contains(fn($i) => !in_array($i->status, [
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED,
            ]));
        }

        return [
            'period' => $period,
            'periodName' => $periodName,
            'unitName' => $unitName,
            'businessAreaName' => $businessAreaName,
            'dateRange' => $dateRange,
            'documentDate' => $documentDate,
            'refNumber' => $refNumber,
            'inspections' => $inspections,
            'formConfig' => $formConfig,
            'logoBase64' => $logoBase64,
            'approverName' => $approverName,
            'approverNipp' => $approverNipp,
            'approverSig' => $approverSig,
            'notesSummary' => $notesSummary,
            'isDraft' => $isDraft,
        ];
    }

    /**
     * Generate consolidated recap PDF for a single InspectionPeriod.
     */
    public function generatePeriodRecapPdf(InspectionPeriod $period): DomPdfInstance
    {
        $inspections = $period->inspections()
            ->with(['employee.unit.businessArea', 'device', 'inspector', 'checklist', 'approvals.user.role', 'notes.user'])
            ->orderBy('document_date')
            ->get();

        $data = $this->prepareRecapReportData($inspections, $period, null);

        return Pdf::loadView('reports.pdf.period_recap', $data)->setPaper('a4', 'landscape');
    }

    /**
     * Generate unit recap PDF for a unit within an InspectionPeriod.
     */
    public function generateUnitRecapPdf(InspectionPeriod $period, Collection $unitInspections, ?Unit $unit): DomPdfInstance
    {
        $unitName = $unit?->name ?? 'Tanpa Unit';
        $data = $this->prepareRecapReportData($unitInspections, $period, $unitName);

        return Pdf::loadView('reports.pdf.period_recap', $data)->setPaper('a4', 'landscape');
    }

    /**
     * Generate consolidated recap PDF for a collection or query of inspections.
     */
    public function generateRecapPdf($inspections, array $meta = []): DomPdfInstance
    {
        if ($inspections instanceof Builder) {
            $inspections = $inspections->with(['employee.unit.businessArea', 'device', 'inspector', 'checklist', 'approvals.user.role', 'notes.user'])
                ->orderBy('document_date', 'desc')
                ->get();
        }

        $data = $this->prepareRecapReportData($inspections, null, null, $meta);

        return Pdf::loadView('reports.pdf.period_recap', $data)->setPaper('a4', 'landscape');
    }

    /**
     * Generate ZIP archive containing:
     * 1. Consolidated recap PDF for ALL units (root)
     * 2. Unit folders, each containing exactly 1 recap PDF for all inspections in that unit.
     */
    public function generatePeriodZip(InspectionPeriod $period): string
    {
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '512M');

        $inspections = $period->inspections()
            ->with(['employee.unit.businessArea', 'device', 'inspector', 'checklist', 'approvals.user.role', 'notes.user'])
            ->orderBy('document_date')
            ->get();

        $safeName = Str::slug($period->name, '_');
        $zipName = "Arsip_Pemeriksaan_{$safeName}_" . now()->format('Ymd_His') . '.zip';
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Tidak dapat membuat file ZIP.");
        }

        // 1. Add Consolidated Recap PDF for ALL units into the ZIP root
        $recapPdf = $this->generatePeriodRecapPdf($period);
        $zip->addFromString("00_REKAPITULASI_PEMERIKSAAN_{$safeName}.pdf", $recapPdf->output());
        unset($recapPdf);
        gc_collect_cycles();

        // 2. Add Unit Recap PDFs (1 PDF per Unit Kerja) inside each unit's folder
        $inspectionsByUnit = $inspections->groupBy(function ($insp) {
            return $insp->employee?->unit_id ?? 0;
        });

        foreach ($inspectionsByUnit as $unitId => $unitInspections) {
            $firstInsp = $unitInspections->first();
            $unit = $firstInsp->employee?->unit;

            $unitRawSlug = Str::slug($unit?->name ?? 'tanpa_unit', '_');
            $unitDir = Str::startsWith($unitRawSlug, 'unit_') ? $unitRawSlug : 'unit_' . $unitRawSlug;

            $unitSafeName = Str::upper($unitDir);
            $fileName = "{$unitDir}/REKAP_PEMERIKSAAN_{$unitSafeName}.pdf";

            $unitPdf = $this->generateUnitRecapPdf($period, $unitInspections, $unit);
            $zip->addFromString($fileName, $unitPdf->output());
            unset($unitPdf);
            gc_collect_cycles();
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Generate ZIP archive for a collection or query of inspections.
     */
    public function generateInspectionsZip($inspections, string $baseName = 'Arsip_Pemeriksaan'): string
    {
        @ini_set('max_execution_time', 300);
        @ini_set('memory_limit', '512M');

        if ($inspections instanceof Builder) {
            $inspections = $inspections->with(['employee.unit.businessArea', 'device', 'inspector', 'checklist', 'approvals.user.role', 'notes.user'])
                ->get();
        }

        $safeName = Str::slug($baseName, '_');
        $zipName = "{$safeName}_" . now()->format('Ymd_His') . '.zip';
        $tempDir = storage_path('app/temp');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $zipPath = $tempDir . '/' . $zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Tidak dapat membuat file ZIP.");
        }

        // 1. Add Consolidated Recap PDF into the ZIP root
        $recapPdf = $this->generateRecapPdf($inspections, ['periodName' => $baseName]);
        $zip->addFromString("00_REKAPITULASI_{$safeName}.pdf", $recapPdf->output());
        unset($recapPdf);
        gc_collect_cycles();

        // 2. Add Unit Recap PDFs (1 PDF per Unit Kerja) inside each unit's folder
        $inspectionsByUnit = $inspections->groupBy(function ($insp) {
            return $insp->employee?->unit_id ?? 0;
        });

        foreach ($inspectionsByUnit as $unitId => $unitInspections) {
            $firstInsp = $unitInspections->first();
            $unit = $firstInsp->employee?->unit;

            $unitRawSlug = Str::slug($unit?->name ?? 'tanpa_unit', '_');
            $unitDir = Str::startsWith($unitRawSlug, 'unit_') ? $unitRawSlug : 'unit_' . $unitRawSlug;

            $unitSafeName = Str::upper($unitDir);
            $fileName = "{$unitDir}/REKAP_PEMERIKSAAN_{$unitSafeName}.pdf";

            $unitName = $unit?->name ?? 'Tanpa Unit';
            $unitData = $this->prepareRecapReportData($unitInspections, null, $unitName, ['periodName' => $baseName]);
            $unitPdf = Pdf::loadView('reports.pdf.period_recap', $unitData)->setPaper('a4', 'landscape');

            $zip->addFromString($fileName, $unitPdf->output());
            unset($unitPdf);
            gc_collect_cycles();
        }

        $zip->close();

        return $zipPath;
    }

    /**
     * Export collection of inspections to Excel file.
     */
    public function exportInspectionsExcel(Builder $query): BinaryFileResponse
    {
        $filename = 'Laporan_Pemeriksaan_PC_Laptop_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new InspectionExport($query), $filename);
    }

    /**
     * Export period inspection recap to Excel.
     */
    public function exportPeriodExcel(InspectionPeriod $period): BinaryFileResponse
    {
        $safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $period->name);
        $filename = "Rekap_Pemeriksaan_{$safeName}_" . now()->format('Ymd') . '.xlsx';
        return Excel::download(new InspectionPeriodExport($period), $filename);
    }
}
