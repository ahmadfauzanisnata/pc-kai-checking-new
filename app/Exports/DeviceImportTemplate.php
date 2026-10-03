<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DeviceImportTemplate implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            'Data Perangkat' => new DeviceImportTemplateSheet(),
            'Petunjuk Pengisian' => new DeviceImportGuideSheet(),
        ];
    }
}
