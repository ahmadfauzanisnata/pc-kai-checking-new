<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Unit;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeImport implements ToModel, WithHeadingRow, WithMultipleSheets
{
    protected ?int $defaultUnitId;

    public function __construct(?int $defaultUnitId = null)
    {
        $this->defaultUnitId = $defaultUnitId;
    }

    /**
     * Only import the first sheet (Data Pegawai), ignoring guide/instruction sheets.
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function model(array $row)
    {
        $nipp = $row['nipp'] ?? null;
        $name = $row['nama_pegawai'] ?? $row['name'] ?? null;

        if (!$nipp || !$name) {
            return null;
        }

        $nippStr = trim((string) $nipp);
        $nameStr = trim((string) $name);

        // Skip if empty, unreasonably long, or contains instruction/header text
        if ($nippStr === '' || $nameStr === '' || strlen($nippStr) > 50) {
            return null;
        }

        $lowerNipp = strtolower($nippStr);
        $lowerName = strtolower($nameStr);
        $invalidKeywords = [
            'petunjuk', 'panduan', 'catatan', 'perhatian', 'keterangan', 'kolom',
            'wajib', 'opsional', 'unik', 'contoh', 'nipp', 'nama_pegawai', 'nama lengkap',
            'hapus', 'format', 'sistem', 'kai pc', 'nomor induk', 'pt kereta api'
        ];
        foreach ($invalidKeywords as $kw) {
            if (str_contains($lowerNipp, $kw) || str_contains($lowerName, $kw)) {
                return null;
            }
        }

        // Skip rows starting with emojis or bullet characters
        if (preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}•\*\-]/u', $nippStr)) {
            return null;
        }

        $unitId = $this->defaultUnitId;
        if (!empty($row['kode_unit'])) {
            $unit = Unit::where('code', trim((string) $row['kode_unit']))->first();
            if ($unit) {
                $unitId = $unit->id;
            }
        }

        if (!$unitId) {
            $unitId = Unit::first()?->id;
        }

        return Employee::updateOrCreate(
            ['nipp' => $nippStr],
            [
                'unit_id' => $unitId,
                'name' => $nameStr,
                'email' => !empty($row['email']) ? trim((string) $row['email']) : null,
                'position' => !empty($row['jabatan'] ?? $row['position'] ?? null) ? trim((string) ($row['jabatan'] ?? $row['position'])) : null,
                'is_active' => true,
            ]
        );
    }
}
