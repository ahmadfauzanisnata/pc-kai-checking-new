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

class DeviceImportTemplateSheet implements FromArray, WithHeadings, WithColumnWidths, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'Data Perangkat';
    }

    public function headings(): array
    {
        return [
            'nomor_aset',
            'hostname',
            'jenis_perangkat',
            'merk',
            'model',
            'serial_number',
            'ip_address',
            'sistem_operasi',
            'versi_os',
            'nipp_pegawai',
            'keterangan',
        ];
    }

    public function array(): array
    {
        return [
            ['AST-PC-001', 'PC-KEUANGAN-01', 'PC', 'Lenovo', 'ThinkCentre M920q', 'SN123456789', '192.168.1.101', 'Windows 11 Pro', '23H2', '1234567890', 'PC Desktop ruang keuangan Lt.2'],
            ['AST-LPT-002', 'LPT-IT-ADMIN-01', 'LAPTOP', 'HP', 'EliteBook 840 G8', 'SN987654321', '192.168.1.150', 'Windows 11 Pro', '24H2', '0987654321', 'Laptop admin IT mobile'],
            ['AST-PC-003', 'PC-OPERASI-05', 'PC', 'Dell', 'OptiPlex 7090', 'SNABC123DEF', '192.168.2.20', 'Windows 10 Pro', '22H2', '1122334455', 'PC ruang operasi'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 22,
            'C' => 18,
            'D' => 16,
            'E' => 24,
            'F' => 20,
            'G' => 18,
            'H' => 20,
            'I' => 14,
            'J' => 18,
            'K' => 35,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = 'K';

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
                    'A1' => "Nomor Aset (WAJIB & UNIK)\nContoh: AST-PC-001\nJika sudah ada, data akan diupdate.",
                    'B1' => "Nama Host / Komputer (Opsional)\nContoh: PC-KEUANGAN-01",
                    'C1' => "Jenis Perangkat (Opsional)\nIsi: PC atau LAPTOP (Default: PC)",
                    'D1' => "Merk / Brand Perangkat (Opsional)\nContoh: Lenovo, HP, Dell",
                    'E1' => "Model / Tipe Perangkat (Opsional)\nContoh: ThinkCentre M920q",
                    'F1' => "Nomor Seri Perangkat (Opsional)\nContoh: SN123456789",
                    'G1' => "Alamat IP Perangkat (Opsional)\nContoh: 192.168.1.101",
                    'H1' => "Sistem Operasi (Opsional)\nDefault: Windows 11 Pro",
                    'I1' => "Versi OS (Opsional)\nDefault: 23H2",
                    'J1' => "NIPP Pegawai PJ (Opsional)\nHarus sesuai NIPP pegawai terdaftar",
                    'K1' => "Keterangan Tambahan (Opsional)\nContoh: PC ruang keuangan Lt.2",
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
