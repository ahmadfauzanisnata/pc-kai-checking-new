<?php

namespace App\Imports;

use App\Models\BusinessArea;
use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    protected ?int $businessAreaId;
    protected int $successCount = 0;
    protected int $failedCount = 0;
    protected array $errors = [];

    public function __construct(?int $businessAreaId = null)
    {
        $this->businessAreaId = $businessAreaId;
    }

    public function collection(Collection $rows): void
    {
        $rowIndex = 1; // Header is row 1

        foreach ($rows as $row) {
            $rowIndex++;

            $nipp = trim((string) ($row['nipp'] ?? $row['nip'] ?? ''));
            $name = trim((string) ($row['nama_pegawai'] ?? $row['nama'] ?? $row['name'] ?? ''));
            $kodeArea = trim((string) ($row['kode_area'] ?? $row['kode_business_area'] ?? $row['business_area'] ?? $row['area'] ?? ''));
            $kodeUnit = trim((string) ($row['kode_unit'] ?? $row['kode_unit_kerja'] ?? $row['kode'] ?? ''));
            $email = trim((string) ($row['email'] ?? ''));
            $position = trim((string) ($row['jabatan'] ?? $row['position'] ?? ''));

            // Ignore completely blank rows
            if ($nipp === '' && $name === '' && $kodeArea === '' && $kodeUnit === '' && $email === '' && $position === '') {
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

            // Validate Kode Unit
            if ($kodeUnit === '') {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode unit kerja wajib diisi.";
                $this->failedCount++;
                continue;
            }

            // Determine Target Business Area
            $targetBa = null;
            if ($kodeArea !== '') {
                $targetBa = BusinessArea::where(function ($q) use ($kodeArea) {
                    $q->where('code', $kodeArea)
                      ->orWhereRaw('LOWER(code) = ?', [strtolower($kodeArea)]);
                })->first();

                if (!$targetBa) {
                    $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode area '{$kodeArea}' tidak ditemukan di sistem.";
                    $this->failedCount++;
                    continue;
                }

                // If user is restricted to a specific Business Area
                if ($this->businessAreaId && $targetBa->id !== $this->businessAreaId) {
                    $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode area '{$kodeArea}' berada di luar Business Area Anda.";
                    $this->failedCount++;
                    continue;
                }
            } elseif ($this->businessAreaId) {
                // If kode_area not in Excel, fallback to the Admin's business area
                $targetBa = BusinessArea::find($this->businessAreaId);
            } else {
                // Super Admin importing without kode_area: check if unit code is unique across active business areas
                $matchingUnits = Unit::where('is_active', true)
                    ->where(function ($q) use ($kodeUnit) {
                        $q->where('code', $kodeUnit)
                          ->orWhereRaw('LOWER(code) = ?', [strtolower($kodeUnit)]);
                    })->get();

                if ($matchingUnits->count() === 1) {
                    $targetBa = $matchingUnits->first()->businessArea;
                } elseif ($matchingUnits->count() > 1) {
                    $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode area wajib diisi karena kode unit '{$kodeUnit}' terdapat pada lebih dari satu Business Area.";
                    $this->failedCount++;
                    continue;
                }
            }

            if (!$targetBa) {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kolom kode_area wajib diisi untuk menentukan Business Area.";
                $this->failedCount++;
                continue;
            }

            // Search Unit in the target Business Area
            $unit = Unit::where('is_active', true)
                ->where('business_area_id', $targetBa->id)
                ->where(function ($q) use ($kodeUnit) {
                    $q->where('code', $kodeUnit)
                      ->orWhereRaw('LOWER(code) = ?', [strtolower($kodeUnit)]);
                })->first();

            if (!$unit) {
                $this->errors[] = "Baris {$rowIndex} (NIPP: {$nipp}, Nama: {$name}): Kode unit '{$kodeUnit}' tidak ditemukan pada Business Area '{$targetBa->name}' ({$targetBa->code}).";
                $this->failedCount++;
                continue;
            }

            // Update or create employee (including restoring soft-deleted records if re-imported)
            $employee = Employee::withTrashed()->where('nipp', $nipp)->first();
            if ($employee) {
                if ($employee->trashed()) {
                    $employee->restore();
                }
                $employee->update([
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'email' => $email !== '' ? $email : null,
                    'position' => $position !== '' ? $position : null,
                    'is_active' => true,
                ]);
            } else {
                Employee::create([
                    'nipp' => $nipp,
                    'unit_id' => $unit->id,
                    'name' => $name,
                    'email' => $email !== '' ? $email : null,
                    'position' => $position !== '' ? $position : null,
                    'is_active' => true,
                ]);
            }

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
