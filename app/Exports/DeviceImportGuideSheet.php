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

class DeviceImportGuideSheet implements FromArray, WithStyles, WithColumnWidths, WithTitle, WithEvents
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
            'B' => 22,
            'C' => 45,
            'D' => 22,
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
                $sheet->setCellValue('A1', 'PANDUAN PENGISIAN TEMPLATE IMPORT PERANGKAT');
                $sheet->mergeCells('A1:E1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1B3A5C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(45);

                $sheet->setCellValue('A2', 'KAI PC Checking System - Panduan Format Data Perangkat');
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
                    ['1', 'nomor_aset', 'Nomor aset perangkat. Digunakan sebagai identitas unik perangkat.', '✅ WAJIB', 'AST-PC-001'],
                    ['2', 'hostname', 'Nama host/komputer di jaringan.', '⚪ Opsional', 'PC-KEUANGAN-01'],
                    ['3', 'jenis_perangkat', 'Jenis perangkat. Isi "PC" untuk PC Desktop atau "LAPTOP" untuk Laptop.', '⚪ Opsional', 'PC atau LAPTOP'],
                    ['4', 'merk', 'Merk/brand perangkat.', '⚪ Opsional', 'Lenovo'],
                    ['5', 'model', 'Model atau tipe perangkat.', '⚪ Opsional', 'ThinkCentre M920q'],
                    ['6', 'serial_number', 'Nomor seri unik perangkat dari pabrikan.', '⚪ Opsional', 'SN123456789'],
                    ['7', 'ip_address', 'Alamat IP perangkat di jaringan lokal.', '⚪ Opsional', '192.168.1.101'],
                    ['8', 'sistem_operasi', 'Nama sistem operasi yang terinstal.', '⚪ Opsional', 'Windows 11 Pro'],
                    ['9', 'versi_os', 'Versi atau build number sistem operasi.', '⚪ Opsional', '23H2'],
                    ['10', 'nipp_pegawai', 'NIPP pegawai penanggung jawab perangkat. Harus sudah terdaftar di master pegawai.', '⚪ Opsional', '1234567890'],
                    ['11', 'keterangan', 'Catatan atau keterangan tambahan tentang perangkat.', '⚪ Opsional', 'PC ruang keuangan Lt.2'],
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
                    '1. Jangan mengubah nama kolom (header) pada sheet "Data Perangkat".',
                    '2. Hapus semua baris contoh sebelum mengisi data sebenarnya.',
                    '3. NOMOR_ASET harus unik. Jika nomor aset sudah ada di sistem, data perangkat akan di-UPDATE.',
                    '4. JENIS_PERANGKAT: isi hanya "PC" atau "LAPTOP" (tidak case-sensitive). Default: PC.',
                    '5. NIPP_PEGAWAI harus sesuai NIPP yang terdaftar di Master Pegawai.',
                    '6. Jika NIPP_PEGAWAI tidak ditemukan atau dikosongkan, perangkat akan ditambahkan tanpa penanggung jawab.',
                    '7. Semua perangkat yang diimport akan otomatis berstatus AKTIF.',
                    '8. File yang didukung: .xlsx, .xls, .csv dengan ukuran maksimal 5MB.',
                    '9. Pastikan tidak ada baris kosong di antara data.',
                    '10. Disarankan untuk import data Pegawai terlebih dahulu sebelum import Perangkat.',
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
