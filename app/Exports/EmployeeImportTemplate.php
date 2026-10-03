<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeImportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data Pegawai' => new EmployeeImportTemplateSheet(),
            'Petunjuk Pengisian' => new EmployeeImportGuideSheet(),
        ];
    }
}
