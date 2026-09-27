<?php

namespace App\Services;

use App\Enums\InspectionStatus;
use App\Exports\InspectionExport;
use App\Exports\InspectionPeriodExport;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Enums\ApprovalDecision;
use App\Models\FormConfiguration;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfInstance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportService
{
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

        $logoBase64 = null;
        if ($formConfig && $formConfig->logo_path) {
            // Prefer the small (resized) version for PDF rendering – DomPDF handles
            // smaller images much more reliably than large originals.
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
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($logoFile));
            }
        }

        $approverApproval = $inspection->approvals
            ->where('decision', ApprovalDecision::APPROVED)
            ->last();

        $approverUser = $approverApproval?->user;
        $approverName = $approverUser?->name;
        $approverNipp = $approverUser?->nipp;

        $approverSig = null;
        $approverSigPath = $approverApproval?->signature_snapshot ?: $approverUser?->signature_path;
        if ($approverSigPath && Storage::disk('public')->exists($approverSigPath)) {
            $content = Storage::disk('public')->get($approverSigPath);
            $mime = Storage::disk('public')->mimeType($approverSigPath) ?: 'image/png';
            $approverSig = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        $inspectorSig = null;
        if ($inspection->inspector && $inspection->inspector->signature_path && Storage::disk('public')->exists($inspection->inspector->signature_path)) {
            $content = Storage::disk('public')->get($inspection->inspector->signature_path);
            $mime = Storage::disk('public')->mimeType($inspection->inspector->signature_path) ?: 'image/png';
            $inspectorSig = 'data:' . $mime . ';base64,' . base64_encode($content);
        }

        $employeeSig = null;
        if ($inspection->employee && $inspection->employee->signature_path && Storage::disk('public')->exists($inspection->employee->signature_path)) {
            $content = Storage::disk('public')->get($inspection->employee->signature_path);
            $mime = Storage::disk('public')->mimeType($inspection->employee->signature_path) ?: 'image/png';
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

        $pdf = Pdf::loadView('reports.pdf.inspection', $data)->setPaper('a4', 'landscape');

        return $pdf;
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
