<?php

namespace App\Imports;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\Employee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DeviceImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $assetNumber = $row['nomor_aset'] ?? $row['asset_number'] ?? null;
        if (!$assetNumber) {
            return null;
        }

        $employeeId = null;
        $nipp = $row['nipp_pegawai'] ?? $row['nipp'] ?? null;
        if ($nipp) {
            $emp = Employee::where('nipp', $nipp)->first();
            if ($emp) {
                $employeeId = $emp->id;
            }
        }

        $typeInput = strtoupper($row['jenis_perangkat'] ?? $row['device_type'] ?? 'PC');
        $deviceType = str_contains($typeInput, 'LAPTOP') ? DeviceType::LAPTOP : DeviceType::PC;

        return Device::updateOrCreate(
            ['asset_number' => $assetNumber],
            [
                'employee_id' => $employeeId,
                'hostname' => $row['hostname'] ?? null,
                'device_type' => $deviceType,
                'brand' => $row['merk'] ?? $row['brand'] ?? null,
                'model' => $row['model'] ?? null,
                'serial_number' => $row['serial_number'] ?? null,
                'ip_address' => $row['ip_address'] ?? null,
                'operating_system' => $row['sistem_operasi'] ?? $row['operating_system'] ?? 'Windows 11 Pro',
                'os_version' => $row['versi_os'] ?? $row['os_version'] ?? '23H2',
                'device_status' => DeviceStatus::ACTIVE,
                'description' => $row['keterangan'] ?? $row['description'] ?? null,
            ]
        );
    }
}
