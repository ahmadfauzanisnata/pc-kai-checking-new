<?php

namespace App\Imports;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class DeviceImport implements ToModel, WithHeadingRow, WithMultipleSheets
{
    /**
     * Only import the first sheet (Data Perangkat), ignoring guide/instruction sheets.
     */
    public function sheets(): array
    {
        return [
            0 => $this,
        ];
    }

    public function model(array $row)
    {
        $assetNumber = $row['nomor_aset'] ?? $row['asset_number'] ?? null;
        if (!$assetNumber) {
            return null;
        }

        $assetStr = trim((string) $assetNumber);

        // Skip if empty, unreasonably long, or contains instruction/header text
        if ($assetStr === '' || strlen($assetStr) > 50) {
            return null;
        }

        $lower = strtolower($assetStr);
        $invalidKeywords = [
            'petunjuk', 'panduan', 'catatan', 'perhatian', 'keterangan', 'kolom',
            'wajib', 'opsional', 'unik', 'contoh', 'nomor_aset', 'asset_number',
            'hapus', 'format', 'sistem', 'kai pc', 'no. aset', 'pt kereta api'
        ];
        foreach ($invalidKeywords as $kw) {
            if (str_contains($lower, $kw)) {
                return null;
            }
        }

        // Skip rows starting with emojis or bullet characters
        if (preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}•\*\-]/u', $assetStr)) {
            return null;
        }

        $employeeId = null;
        $nipp = $row['nipp_pegawai'] ?? $row['nipp'] ?? null;
        if ($nipp) {
            $emp = Employee::where('nipp', trim((string) $nipp))->first();
            if ($emp) {
                $employeeId = $emp->id;
            }
        }

        $typeInput = strtoupper($row['jenis_perangkat'] ?? $row['device_type'] ?? 'PC');
        $deviceType = str_contains($typeInput, 'LAPTOP') ? DeviceType::LAPTOP : DeviceType::PC;

        return Device::updateOrCreate(
            ['asset_number' => $assetStr],
            [
                'employee_id' => $employeeId,
                'hostname' => !empty($row['hostname']) ? trim((string) $row['hostname']) : null,
                'device_type' => $deviceType,
                'brand' => !empty($row['merk'] ?? $row['brand'] ?? null) ? trim((string) ($row['merk'] ?? $row['brand'])) : null,
                'model' => !empty($row['model']) ? trim((string) $row['model']) : null,
                'serial_number' => !empty($row['serial_number']) ? trim((string) $row['serial_number']) : null,
                'ip_address' => !empty($row['ip_address']) ? trim((string) $row['ip_address']) : null,
                'operating_system' => $row['sistem_operasi'] ?? $row['operating_system'] ?? 'Windows 11 Pro',
                'os_version' => $row['versi_os'] ?? $row['os_version'] ?? '23H2',
                'device_status' => DeviceStatus::ACTIVE,
                'description' => !empty($row['keterangan'] ?? $row['description'] ?? null) ? trim((string) ($row['keterangan'] ?? $row['description'])) : null,
            ]
        );
    }
}
