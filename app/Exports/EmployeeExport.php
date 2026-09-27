<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeeExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $unitId;

    public function __construct(?int $unitId = null)
    {
        $this->unitId = $unitId;
    }

    public function collection()
    {
        $query = Employee::with('unit.businessArea');
        if ($this->unitId) {
            $query->where('unit_id', $this->unitId);
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Business Area',
            'Unit Kerja',
            'NIPP',
            'Nama Pegawai',
            'Email',
            'Jabatan',
            'Status Aktif',
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->unit?->businessArea?->name ?? '-',
            $employee->unit?->name ?? '-',
            $employee->nipp,
            $employee->name,
            $employee->email,
            $employee->position,
            $employee->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
