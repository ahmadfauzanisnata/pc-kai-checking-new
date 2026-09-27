<?php

namespace App\Exports;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InspectionExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected Builder $query;
    protected int $rowNumber = 0;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function collection()
    {
        return $this->query->with([
            'employee.unit.businessArea',
            'device',
            'inspector',
            'inspectionPeriod.businessArea',
            'checklist',
        ])->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Referensi',
            'Tanggal Pemeriksaan',
            'Business Area',
            'Unit Kerja',
            'Nama Pegawai',
            'NIPP',
            'Jabatan',
            'Hostname Perangkat',
            'No. Aset',
            'Tipe Perangkat',
            'Sistem Operasi',
            'Status Kepatuhan / Temuan',
            'Nama Pemeriksa',
            'Status Pemeriksaan',
            'Tanggal Disetujui',
        ];
    }

    /**
     * @param Inspection $inspection
     */
    public function map($inspection): array
    {
        $this->rowNumber++;
        $findingsCount = $inspection->checklist ? $inspection->checklist->getNegativeFindingsCount() : 0;
        $findingSummary = $findingsCount > 0 ? "{$findingsCount} Temuan Kepatuhan" : 'Sesuai Standar';

        return [
            $this->rowNumber,
            $inspection->reference_number,
            $inspection->document_date ? $inspection->document_date->format('d/m/Y') : '-',
            $inspection->inspectionPeriod?->businessArea?->code ?? '-',
            $inspection->employee?->unit?->name ?? '-',
            $inspection->employee?->name ?? '-',
            $inspection->employee?->nipp ?? '-',
            $inspection->employee?->position ?? '-',
            $inspection->device?->hostname ?? '-',
            $inspection->device?->asset_number ?? '-',
            $inspection->device?->device_type?->label() ?? '-',
            ($inspection->device?->os_name ?? '') . ' ' . ($inspection->device?->os_version ?? ''),
            $findingSummary,
            $inspection->inspector?->name ?? '-',
            $inspection->status->label(),
            $inspection->approved_at ? $inspection->approved_at->format('d/m/Y H:i') : '-',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];
    }
}
