<?php

namespace App\Imports;

use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class EmployeeImport implements ToCollection, WithHeadingRow, WithMultipleSheets
{
    protected ?int $businessAreaId;
    protected int $successCount = 0;
    protected int $failedCount = 0;
    protected array $errors = [];

    public function __construct(?int $businessAreaId = null)
    {
        $this->businessAreaId = $businessAreaId;
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

    public function collection(Collection $rows): void
    {
        $rowIndex = 1; // Header is row 1

        foreach ($rows as $row) {
            $rowIndex++;

            $nipp = trim((string) ($row['nipp'] ?? $row['nip'] ?? ''));
            $name = trim((string) ($row['nama_pegawai'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $kodeUnit = trim((string) ($row['kode_unit'] ?? $row['kode_unit_kerja'] ?? $row['kode'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $position = trim((string) ($row['jabatan'] ?? $row['position'] ?? ''));

            // Ignore completely blank rows
            if ($nipp === '' && $name === '' && $kodeUnit === '' && $email === '' && $position === '') {
                continue;
            }

            // Skip guide / instruction / template description text rows
            $lowerNipp = strtolower($nipp);
            $lowerName = strtolower($name);
            $invalidKeywords = [
                'petunjuk', 'panduan', 'catatan', 'perhatian', 'keterangan', 'kolom',
                'wajib', 'opsional', 'unik', 'contoh', 'nama_pegawai', 'nama lengkap',
                'hapus', 'format', 'sistem', 'kai pc', 'nomor induk', 'pt kereta api'
            ];
            $isInstruction = false;
            foreach ($invalidKeywords as $kw) {
                if (str_contains($lowerNipp, $kw) || str_contains($lowerName, $kw)) {
                    $isInstruction = true;
                    break;
                }
            }
            if ($isInstruction) {
                continue;
            }

            // Skip rows starting with emojis or bullet characters
            if (preg_match('/^[\x{1F300}-\x{1F9FF}\x{2600}-\x{26FF}\x{2700}-\x{27BF}•\*\-]/u', $nipp)) {
                continue;
            }

            // Validate NIPP
            if ($nipp === '') {
                $this->errors[] = "Baris {$rowIndex}: Kolom NIPP wajib diisi.";
                $this->failedCount++;
                continue;
            }

            if (strlen($nipp) > 50) {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}): NIPP terlalu panjang (maksimal 50 karakter).";
                $this->failedCount++;
                continue;
            }

            // Validate Nama Pegawai
            if ($name === '') {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}): Nama pegawai wajib diisi.";
                $this->failedCount++;
                continue;
            }

            // Validate Kode Unit (must be filled and must match existing Unit)
            if ($kodeUnit === '') {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode unit kerja wajib diisi.";
                $this->failedCount++;
                continue;
            }

            // Search unit by code (case-insensitive)
            $unitQuery = Unit::where('is_active', true);
            if ($this->businessAreaId) {
                $unitQuery->where('business_area_id', $this->businessAreaId);
            }

            $unit = (clone $unitQuery)->where(function ($q) use ($kodeUnit) {
                $q->where('code', $kodeUnit)
                  ->orWhereRaw('LOWER(code) = ?', [strtolower($kodeUnit)]);
            })->first();

            // If not found in current business area, check if it exists in another business area
            if (!$unit && $this->businessAreaId) {
                $otherUnit = Unit::where('is_active', true)
                    ->where(function ($q) use ($kodeUnit) {
                        $q->where('code', $kodeUnit)
                          ->orWhereRaw('LOWER(code) = ?', [strtolower($kodeUnit)]);
                    })->first();

                if ($otherUnit) {
                    $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode unit '{$kodeUnit}' berada di luar Business Area Anda.";
                    $this->failedCount++;
                    continue;
                }
            }

            if (!$unit) {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode unit '{$kodeUnit}' tidak ditemukan di sistem.";
                $this->failedCount++;
                continue;
            }

            // Update or create employee
            Employee::updateOrCreate(
                ['nipp' => $nipp],
                [
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'email' => $email !== '' ? $email : null,
                    'position' => $position !== '' ? $position : null,
                    'is_active' => true,
                ]
            );

            $this->successCount++;
        }
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getFailedCount(): int
    {
        return $this->failedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
