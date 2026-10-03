<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class EmployeeImportGuideSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Petunjuk Pengisian';
    }

    public function array(): array
    {
        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 25,
            'C' => 45,
            'D' => 25,
            'E' => 30,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // ============================
                // TITLE
                // ============================
                $sheet->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE IMPORT PEGAWAI');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B3A5C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(45);

                $sheet->setCellValue('A2', 'KAI PC Checking System - Panduan Format Data');
                $sheet->mergeCells('A2:E2');
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2C5F8A']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(2)->setRowHeight(25);

                // ============================
                // COLUMN GUIDE TABLE
                // ============================
                $row = 4;
                $tableHeaders = ['No', 'Nama Kolom', 'Deskripsi', 'Wajib/Opsional', 'Contoh Isi'];
                $cols = ['A', 'B', 'C', 'D', 'E'];

                foreach ($tableHeaders as $i => $header) {
                    $sheet->setCellValue("{$cols[$i]}{$row}", $header);
                }

                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F47920']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D35400']]],
                ]);
                $sheet->getRowDimension($row)->setRowHeight(28);

                // Column details
                $columns = [
                    ['1', 'nipp', 'Nomor Induk Pegawai Perusahaan. Digunakan sebagai identitas unik pegawai.', '✅ WAJIB', '1234567890'],
                    ['2', 'nama_pegawai', 'Nama lengkap pegawai sesuai data resmi.', '✅ WAJIB', 'Ahmad Fauzan'],
                    ['3', 'kode_unit', 'Kode unit kerja yang sudah terdaftar di sistem. Jika kosong, menggunakan unit default.', '⚪ Opsional', 'UPT-01'],
                    ['4', 'email', 'Alamat email resmi pegawai.', '⚪ Opsional', 'ahmad@kai.id'],
                    ['5', 'jabatan', 'Jabatan atau posisi pegawai.', '⚪ Opsional', 'Staff IT'],
                ];

                foreach ($columns as $i => $colData) {
                    $r = $row + 1 + $i;
                    foreach ($colData as $j => $value) {
                        $sheet->setCellValue("{$cols[$j]}{$r}", $value);
                    }

                    $isEven = $i % 2 === 0;
                    $bgColor = $isEven ? 'F8F9FA' : 'FFFFFF';
                    $sheet->getStyle("A{$r}:E{$r}")->applyFromArray([
                        'font' => ['size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);

                    // Highlight required columns
                    $requiredColor = str_contains($colData[3], 'WAJIB') ? 'FDEDEC' : 'EAFAF1';
                    $sheet->getStyle("D{$r}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 10],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $requiredColor]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $sheet->getRowDimension($r)->setRowHeight(30);
                }

                // ============================
                // IMPORTANT NOTES
                // ============================
                $notesRow = $row + count($columns) + 3;
                $sheet->setCellValue("A{$notesRow}", '📌 CATATAN PENTING');
                $sheet->mergeCells("A{$notesRow}:E{$notesRow}");
                $sheet->getStyle("A{$notesRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'color' => ['rgb' => '1B3A5C']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EBF5FB']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1B3A5C']]],
                ]);
                $sheet->getRowDimension($notesRow)->setRowHeight(32);

                $notes = [
                    '1. Jangan mengubah nama kolom (header) pada sheet "Data Pegawai".',
                    '2. Hapus semua baris contoh sebelum mengisi data sebenarnya.',
                    '3. NIPP harus unik. Jika NIPP sudah ada di sistem, data pegawai akan di-UPDATE (bukan duplikat).',
                    '4. Pastikan KODE_UNIT sesuai dengan kode unit yang terdaftar di Master Unit Kerja.',
                    '5. File yang didukung: .xlsx, .xls, .csv dengan ukuran maksimal 5MB.',
                    '6. Pastikan tidak ada baris kosong di antara data.',
                    '7. Semua pegawai yang diimport akan otomatis berstatus AKTIF.',
                ];

                foreach ($notes as $i => $note) {
                    $r = $notesRow + 1 + $i;
                    $sheet->setCellValue("A{$r}", $note);
                    $sheet->mergeCells("A{$r}:E{$r}");
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'font' => ['size' => 10, 'color' => ['rgb' => '444444']],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension($r)->setRowHeight(22);
                }

                // Print setup
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}
