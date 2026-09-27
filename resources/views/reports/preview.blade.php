<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Formulir: {{ $inspection->reference_number }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 15px;
            color: #000;
        }
        .page {
            background: #ffffff;
            width: 297mm;
            min-height: 210mm;
            padding: 10mm 12mm;
            margin: 0 auto;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            box-sizing: border-box;
            position: relative;
        }
        @media print {
            body { background: transparent; padding: 0; }
            .no-print { display: none !important; }
            .page { width: 100%; box-shadow: none; margin: 0; padding: 6mm 8mm; }
        }
        .watermark {
            position: absolute;
            top: 40%;
            left: 20%;
            transform: rotate(-25deg);
            font-size: 60px;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.12);
            border: 3px dashed rgba(220, 38, 38, 0.20);
            padding: 15px 35px;
            pointer-events: none;
            z-index: 10;
        }
        table {
            border-collapse: collapse;
        }
        .header-table {
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 6px;
        }
        .header-table td {
            border: 0.5px solid #000;
        }
        .meta-table {
            border: 0.5px solid #000;
            margin-top: 6px;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .meta-table td {
            border: 0.5px solid #000;
            padding: 2px 6px;
        }
        .period-info {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 8px;
        }
        .grid-table {
            width: 100%;
            border: 0.5px solid #000;
            font-size: 9.5px;
        }
        .grid-table th {
            border: 0.5px solid #000;
            background-color: #BFBFBF;
            text-align: center;
            vertical-align: middle;
            font-weight: bold;
            padding: 4px 2px;
            line-height: 1.15;
        }
        .grid-table td {
            border: 0.5px solid #000;
            padding: 3px 2px;
            vertical-align: middle;
            height: 18px;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        
        .footer-table {
            width: 100%;
            margin-top: 10px;
        }
        .catatan-box {
            border: 0.5px solid #000;
            padding: 6px 8px;
            height: 80px;
            vertical-align: top;
            font-size: 10px;
        }
        .sign-col {
            vertical-align: top;
            text-align: center;
            font-size: 11px;
        }
    </style>
</head>
<body>

    <!-- Action Toolbar (No Print) -->
    <div class="no-print" style="max-width: 297mm; margin: 0 auto 12px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <a href="{{ url()->previous() }}" style="text-decoration: none; color: #1E3A8A; font-weight: bold; font-size: 13px;">
                &larr; Kembali
            </a>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" style="padding: 7px 16px; background-color: #1E3A8A; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 13px;">
                Cetak Formulir Ini (Print)
            </button>
            <a href="{{ route('reports.pdf', $inspection) }}" target="_blank" style="padding: 7px 16px; background-color: #ED6B22; color: white; border-radius: 6px; text-decoration: none; font-weight: bold; font-size: 13px;">
                Unduh PDF Resmi
            </a>
        </div>
    </div>

    <!-- Page Printable Container -->
    <div class="page">
        @if($isDraft)
        <div class="watermark">DRAFT / BELUM DISETUJUI</div>
        @endif

        <!-- ============================================== -->
        <!-- 1. HEADER SECTION                              -->
        <!-- ============================================== -->
        <table class="header-table" style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 6px;">
            <tr>
                <!-- Col 1: Logo & TERBATAS -->
                <td style="width: 20%; border: 0.5px solid #000; padding: 0; vertical-align: top;">
                    <div style="height: 48px; text-align: center; padding-top: 6px;">
                        @if(!empty($logoBase64))
                            <img src="{{ $logoBase64 }}" style="max-height: 40px; max-width: 125px;" alt="KAI Logo">
                        @else
                            <div style="font-size: 26px; font-weight: 900; color: #1D2A68; letter-spacing: -1px;">
                                K<span style="color: #ED6B22;">A</span>I
                            </div>
                        @endif
                    </div>
                    <div style="border-top: 0.5px solid #000; text-align: center; padding: 4px 0; background-color: #fff;">
                        <div style="display: inline-block; border: 2px solid #FFD700; padding: 1px 12px; background-color: #FFFFFF;">
                            <span style="color: #FFCC00; font-weight: 900; font-size: 12px; letter-spacing: 1.5px;">TERBATAS</span>
                        </div>
                    </div>
                </td>

                <!-- Col 2: Company Title & Form Name -->
                <td style="width: 52%; border: 0.5px solid #000; padding: 0; vertical-align: top; text-align: center;">
                    <div style="height: 48px; padding-top: 6px;">
                        <div style="font-size: 13px; font-weight: bold; text-transform: uppercase;">
                            {{ $formConfig?->company_name ?? 'PT. KERETA API INDONESIA (PERSERO)' }}
                        </div>
                        <div style="font-size: 11.5px; font-weight: bold; margin-top: 2px;">
                            {{ $formConfig?->department_name ?? 'Sistem Informasi' }}
                        </div>
                    </div>
                    <div style="border-top: 0.5px solid #000; padding: 5px 0;">
                        <div style="font-size: 13px; font-weight: 900; letter-spacing: 0.5px;">
                            FORMULIR PC/LAPTOP CHECKING
                        </div>
                    </div>
                </td>

                <!-- Col 3: Document Metadata -->
                <td style="width: 28%; border: 0.5px solid #000; padding: 0; vertical-align: top;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 10.5px; height: 100%;">
                        <tr>
                            <td style="width: 38%; padding: 3px 5px; border-bottom: 0.5px solid #000;">Nomor</td>
                            <td style="width: 4%; padding: 3px 0; border-bottom: 0.5px solid #000; text-align: center;">:</td>
                            <td style="width: 58%; padding: 3px 5px; border-bottom: 0.5px solid #000;">{{ $formConfig?->form_number ?? 'FR.SM/TI/017.002/10-2020' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 5px; border-bottom: 0.5px solid #000;">Tanggal Terbit</td>
                            <td style="padding: 3px 0; border-bottom: 0.5px solid #000; text-align: center;">:</td>
                            <td style="padding: 3px 5px; border-bottom: 0.5px solid #000;">{{ $formConfig?->publication_date ? $formConfig->publication_date->translatedFormat('d F Y') : '12 Oktober 2020' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 5px; border-bottom: 0.5px solid #000;">Status Revisi</td>
                            <td style="padding: 3px 0; border-bottom: 0.5px solid #000; text-align: center;">:</td>
                            <td style="padding: 3px 5px; border-bottom: 0.5px solid #000;">{{ $formConfig?->revision_status ?? '002-2020' }}</td>
                        </tr>
                        <tr>
                            <td style="padding: 3px 5px;">Halaman</td>
                            <td style="padding: 3px 0; text-align: center;">:</td>
                            <td style="padding: 3px 5px;">{{ $formConfig?->page_label ?? '1 dari 1' }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- ============================================== -->
        <!-- 2. METADATA SECTION                             -->
        <!-- ============================================== -->
        <table class="meta-table" style="width: 320px;">
            <tr>
                <td style="width: 95px;">No. Ref</td>
                <td>: {{ $inspection->reference_number ?? '__/__/____' }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ $inspection->inspection_date ? $inspection->inspection_date->format('d-m-Y') : ($inspection->document_date ? $inspection->document_date->format('d-m-Y') : '__-__-____') }}</td>
            </tr>
            <tr>
                <td>Business Area</td>
                <td>: {{ $inspection->inspectionPeriod?->businessArea?->name ?? ($inspection->employee?->unit?->businessArea?->name ?? 'Daop 2 Bandung') }}</td>
            </tr>
        </table>

        <div class="period-info">
            <div><strong>Periode Pemeriksaan :</strong> {{ $inspection->inspectionPeriod?->name ?? '-' }}</div>
            <div><strong>Tanggal Pemeriksaan :</strong> {{ $inspection->inspection_date ? $inspection->inspection_date->translatedFormat('d F Y') : ($inspection->inspectionPeriod?->start_date ? $inspection->inspectionPeriod->start_date->translatedFormat('d F Y') . ' s/d ' . $inspection->inspectionPeriod->end_date->translatedFormat('d F Y') : '-') }}</div>
        </div>

        <!-- ============================================== -->
        <!-- 3. MAIN SPREADSHEET GRID (17 COLUMNS)           -->
        <!-- ============================================== -->
        <table class="grid-table">
            <thead>
                <!-- Header Row 1 -->
                <tr>
                    <th rowspan="3" style="width: 2.5%;">NO</th>
                    <th rowspan="3" style="width: 12%;">NAMA PENGGUNA</th>
                    <th rowspan="3" style="width: 5.5%;">UNIT</th>
                    <th colspan="12" style="width: 68%;">CHECKLIST</th>
                    <th colspan="2" style="width: 12%;">Verifikasi / Paraf</th>
                </tr>
                <!-- Header Row 2 -->
                <tr>
                    <th rowspan="2" style="width: 5.5%;">NDA<br>(Sudah/Belum)</th>
                    <th rowspan="2" style="width: 6.5%;">Login<br>Strong<br>Password<br>(Sudah/Belum)</th>
                    <th rowspan="2" style="width: 6.5%;">Screensaver<br>Lock<br>(maks 5<br>menit)<br>(Sudah/Belum)</th>
                    <th rowspan="2" style="width: 5.5%;">* Hak<br>Akses<br>Khusus<br>(Admin /<br>User)</th>
                    <th rowspan="2" style="width: 5.5%;">Cleardesk<br>(Sudah/<br>Belum)</th>
                    <th rowspan="2" style="width: 5%;">.mp3,<br>video,<br>etc<br>(Ada/Tidak)</th>
                    <th colspan="3" style="width: 17%;">Antivirus</th>
                    <th rowspan="2" style="width: 5.5%;">O/S<br>(License /<br>Tidak)</th>
                    <th rowspan="2" style="width: 5.5%;">Sinkronisasi<br>NTP<br>Server<br>(Ya/Tidak)</th>
                    <th rowspan="2" style="width: 5%;">Label PC<br>(Ada/Tidak)</th>
                    <th rowspan="2" style="width: 6%;">Pemeriksa</th>
                    <th rowspan="2" style="width: 6%;">Pegawai<br>Ybs</th>
                </tr>
                <!-- Header Row 3 -->
                <tr>
                    <th style="width: 5.5%;">Status<br>Install<br>(Sudah/<br>Belum)</th>
                    <th style="width: 5.5%;">Status<br>Update<br>(Sudah/<br>Belum)</th>
                    <th style="width: 6%;">Full Scan<br>Auto<br>Schedule<br>(Sudah/Belum)</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $c = $inspection->checklist;
                @endphp

                <!-- Row 1: Primary Inspected Device Data -->
                <tr>
                    <td class="text-center font-bold">1</td>
                    <td class="text-left font-bold" style="padding-left: 4px;">{{ $inspection->employee?->name ?? '-' }}</td>
                    <td class="text-center">{{ $inspection->employee?->unit?->code ?? ($inspection->employee?->unit?->name ?? '-') }}</td>
                    <td class="text-center">{{ $c?->nda_status ? ucfirst(strtolower($c->nda_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->strong_password_status ? ucfirst(strtolower($c->strong_password_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->screensaver_lock_status ? ucfirst(strtolower($c->screensaver_lock_status)) : '-' }}</td>
                    <td class="text-center">{{ is_object($c?->special_access) ? $c->special_access->label() : ($c?->special_access ? ucfirst(strtolower($c->special_access)) : '-') }}</td>
                    <td class="text-center">{{ $c?->cleardesk_status ? ucfirst(strtolower($c->cleardesk_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->prohibited_files_status ? ucfirst(strtolower($c->prohibited_files_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->antivirus_install_status ? ucfirst(strtolower($c->antivirus_install_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->antivirus_update_status ? ucfirst(strtolower($c->antivirus_update_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->full_scan_schedule_status ? ucfirst(strtolower($c->full_scan_schedule_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->os_license_status === 'LICENSE' ? 'License' : ($c?->os_license_status ? ucfirst(strtolower($c->os_license_status)) : '-') }}</td>
                    <td class="text-center">{{ $c?->ntp_sync_status ? ucfirst(strtolower($c->ntp_sync_status)) : '-' }}</td>
                    <td class="text-center">{{ $c?->pc_label_status ? ucfirst(strtolower($c->pc_label_status)) : '-' }}</td>
                    <td class="text-center" style="vertical-align: middle;">
                        @if(!empty($inspectorSig))
                            <img src="{{ $inspectorSig }}" style="max-height: 20px; max-width: 45px;">
                        @elseif($inspection->inspector)
                            <span style="font-size: 9px; font-weight: bold; color: #1e3a8a;">&#10003; {{ Str::limit($inspection->inspector->name, 8, '') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center" style="vertical-align: middle;">
                        @if(!empty($employeeSig))
                            <img src="{{ $employeeSig }}" style="max-height: 20px; max-width: 45px;">
                        @elseif($inspection->employee)
                            <span style="font-size: 9px; font-weight: bold; color: #1e3a8a;">&#10003; {{ Str::limit($inspection->employee->name, 8, '') }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>

                <!-- Rows 2 to 10 (Grid Template Rows) -->
                @for($i = 2; $i <= 10; $i++)
                <tr>
                    <td class="text-center">{{ $i }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor

                <!-- Row <n> -->
                <tr>
                    <td class="text-center">&lt;n&gt;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </tbody>
        </table>

        <!-- ============================================== -->
        <!-- 4. FOOTER SECTION: CATATAN & MENGETAHUI         -->
        <!-- ============================================== -->
        <table class="footer-table">
            <tr>
                <!-- Catatan Box (Left) -->
                <td class="catatan-box" style="width: 55%;">
                    <div style="font-weight: bold; margin-bottom: 3px;">Catatan :</div>
                    <div style="line-height: 1.4;">
                        @if(!empty($c?->finding_summary))
                            {{ $c->finding_summary }}
                        @elseif($inspection->notes && $inspection->notes->count() > 0)
                            {{ $inspection->notes->pluck('content')->implode('; ') }}
                        @else
                            -
                        @endif
                    </div>
                </td>

                <!-- Empty Spacer -->
                <td style="width: 5%;"></td>

                <!-- Signature Area (Right) -->
                <td class="sign-col" style="width: 40%;">
                    <div style="border-bottom: 0.5px dashed #000; display: inline-block; padding-bottom: 1px; min-width: 160px;">
                        {{ $inspection->inspectionPeriod?->businessArea?->name ?? '.......................' }}, 
                        {{ $inspection->document_date ? $inspection->document_date->translatedFormat('d F Y') : ($inspection->inspection_date ? $inspection->inspection_date->translatedFormat('d F Y') : '......................................') }}
                    </div>

                    <div style="margin-top: 6px; font-weight: bold;">Mengetahui,</div>

                    <div style="height: 48px; margin: 2px 0; display: flex; align-items: center; justify-content: center;">
                        @if(in_array($inspection->status, [\App\Enums\InspectionStatus::APPROVED, \App\Enums\InspectionStatus::LOCKED, \App\Enums\InspectionStatus::ARCHIVED]))
                            @if(!empty($approverSig))
                                <img src="{{ $approverSig }}" style="max-height: 46px; max-width: 130px; object-fit: contain;" alt="Tanda Tangan Mengetahui">
                            @endif
                        @endif
                    </div>

                    <div style="border-bottom: 0.5px dashed #000; display: inline-block; min-width: 170px; font-weight: bold;">
                        {{ $approverName ?? '......................................................' }}
                    </div>
                    <div style="margin-top: 2px;">
                        NIPP. {{ $approverNipp ?? '.........................' }}
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
