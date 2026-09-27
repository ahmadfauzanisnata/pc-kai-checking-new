<?php

namespace App\Exports;

use App\Models\Inspection;
use App\Models\InspectionPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class InspectionPeriodExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected InspectionPeriod $period;
    protected int $rowNumber = 0;

    public function __construct(InspectionPeriod $period)
    {
        $this->period = $period;
    }

    public function collection()
    {
        return Inspection::with([
            'employee.unit',
            'device',
            'inspector',
            'checklist',
        ])->where('inspection_period_id', $this->period->id)->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Referensi',
            'Unit Kerja',
            'Nama Pegawai',
            'NIPP',
            'Hostname',
            'No. Aset',
            'Tipe',
            'Pemeriksa',
            'Status',
            'Temuan Kepatuhan',
            'Tanggal Pemeriksaan',
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

        return [
            $this->rowNumber,
            $inspection->reference_number,
            $inspection->employee?->unit?->name ?? '-',
            $inspection->employee?->name ?? '-',
            $inspection->employee?->nipp ?? '-',
            $inspection->device?->hostname ?? '-',
            $inspection->device?->asset_number ?? '-',
            $inspection->device?->device_type?->label() ?? '-',
            $inspection->inspector?->name ?? '-',
            $inspection->status->label(),
            $findingsCount > 0 ? "{$findingsCount} Temuan" : 'Nihil / Sesuai',
            $inspection->document_date ? $inspection->document_date->format('d/m/Y') : '-',
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
                    'startColor' => ['argb' => 'FFED6B22'], // KAI Orange
                ],
            ],
        ];
    }
}
