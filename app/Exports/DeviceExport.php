<?php

namespace App\Exports;

use App\Models\Device;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeviceExport implements FromCollection, WithHeadings, WithMapping
{
    protected ?int $unitId;

    public function __construct(?int $unitId = null)
    {
        $this->unitId = $unitId;
    }

    public function collection()
    {
        $query = Device::with(['employee.unit']);
        if ($this->unitId) {
            $query->whereHas('employee', function ($q) {
                $q->where('unit_id', $this->unitId);
            });
        }
        return $query->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nomor Aset',
            'Hostname',
            'Jenis Perangkat',
            'Merk',
            'Model',
            'Serial Number',
            'IP Address',
            'Sistem Operasi',
            'Versi OS',
            'Status Perangkat',
            'Pegawai Penanggung Jawab',
            'NIPP Pegawai',
            'Unit Kerja',
        ];
    }

    public function map($device): array
    {
        return [
            $device->id,
            $device->asset_number,
            $device->hostname,
            $device->device_type?->label() ?? $device->device_type,
            $device->brand,
            $device->model,
            $device->serial_number,
            $device->ip_address,
            $device->operating_system,
            $device->os_version,
            $device->device_status?->label() ?? $device->device_status,
            $device->employee?->name ?? 'Belum Ditugaskan',
            $device->employee?->nipp ?? '-',
            $device->employee?->unit?->name ?? '-',
        ];
    }
}
