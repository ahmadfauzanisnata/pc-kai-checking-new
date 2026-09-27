<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToModel, WithHeadingRow
{
    protected ?int $defaultUnitId;

    public function __construct(?int $defaultUnitId = null)
    {
        $this->defaultUnitId = $defaultUnitId;
    }

    public function model(array $row)
    {
        $nipp = $row['nipp'] ?? null;
        $name = $row['nama_pegawai'] ?? $row['name'] ?? null;

        if (!$nipp || !$name) {
            return null;
        }

        $unitId = $this->defaultUnitId;
        if (!empty($row['kode_unit'])) {
            $unit = Unit::where('code', $row['kode_unit'])->first();
            if ($unit) {
                $unitId = $unit->id;
            }
        }

        if (!$unitId) {
            $unitId = Unit::first()?->id;
        }

        return Employee::updateOrCreate(
            ['nipp' => $nipp],
            [
                'unit_id' => $unitId,
                'name' => $name,
                'email' => $row['email'] ?? null,
                'position' => $row['jabatan'] ?? $row['position'] ?? null,
                'is_active' => true,
            ]
        );
    }
}
