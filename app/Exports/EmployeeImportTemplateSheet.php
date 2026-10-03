<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class EmployeeImportTemplateSheet implements FromArray, WithHeadings, WithColumnWidths, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Data Pegawai';
    }

    public function headings(): array
    {
        return [
            'nipp',
            'nama_pegawai',
            'kode_unit',
            'email',
            'jabatan',
        ];
    }

    public function array(): array
    {
        return [
            ['1234567890', 'Ahmad Fauzan', 'UPT-01', 'ahmad.fauzan@kai.id', 'Staff IT'],
            ['0987654321', 'Siti Rahmawati', 'UPT-02', 'siti.rahmawati@kai.id', 'Supervisor Operasi'],
            ['1122334455', 'Budi Santoso', 'UPT-01', 'budi.santoso@kai.id', 'Manager Keuangan'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 30,
            'C' => 18,
            'D' => 35,
            'E' => 28,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'E';

                // ============================
                // HEADER ROW (Row 1) — Professional KAI Orange Styling
                // ============================
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => 'FFFFFF'],
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F47920'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'D35400'],
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(32);

                // ============================
                // CELL COMMENTS ON HEADERS (Tooltips for each column)
                // ============================
                $comments = [
                    'A1' => "Nomor Induk Pegawai (WAJIB & UNIK)\nContoh: 1234567890\nJika sudah ada, data akan diupdate.",
                    'B1' => "Nama Lengkap Pegawai (WAJIB)\nContoh: Ahmad Fauzan",
                    'C1' => "Kode Unit Kerja (Opsional)\nContoh: UPT-01\nJika kosong, memakai unit default.",
                    'D1' => "Alamat Email Pegawai (Opsional)\nContoh: ahmad.fauzan@kai.id",
                    'E1' => "Jabatan / Posisi Pegawai (Opsional)\nContoh: Staff IT",
                ];

                foreach ($comments as $cell => $commentText) {
                    $comment = $sheet->getComment($cell);
                    $comment->getText()->createTextRun($commentText);
                    $comment->setWidth('240pt');
                    $comment->setHeight('65pt');
                }

                // ============================
                // EXAMPLE DATA ROWS (Rows 2 to 4)
                // ============================
                for ($row = 2; $row <= 4; $row++) {
                    $isEven = $row % 2 === 0;
                    $bgColor = $isEven ? 'F8F9FA' : 'FFFFFF';

                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font' => ['size' => 10, 'name' => 'Calibri'],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $bgColor]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
                    ]);
                    $sheet->getRowDimension($row)->setRowHeight(22);
                }

                // Freeze panes below header row
                $sheet->freezePane('A2');

                // Print setup
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setFitToWidth(1);
            },
        ];
    }
}
