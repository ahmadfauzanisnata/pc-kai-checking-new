<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Formulir PC/Laptop Checking - {{ $periodName }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 5mm 7mm 5mm 7mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7pt;
            color: #000;
            line-height: 1.1;
            margin: 0;
            padding: 0;
        }
        .watermark {
            position: fixed;
            top: 35%;
            left: 18%;
            transform: rotate(-25deg);
            font-size: 50pt;
            font-weight: bold;
            color: rgba(220, 38, 38, 0.12);
            border: 3pt dashed rgba(220, 38, 38, 0.20);
            padding: 15px 35px;
            z-index: -1000;
        }
        table {
            border-collapse: collapse;
        }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        tr {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>

    @if(!empty($isDraft))
    <div class="watermark">DRAFT / BELUM DISETUJUI</div>
    @endif

    {{-- ================================================ --}}
    {{-- 1. HEADER TABLE                                  --}}
    {{-- ================================================ --}}
    <table style="width: 100%; border: 0.5pt solid #000; border-collapse: collapse; margin-bottom: 3px;">
        <tr>
            {{-- Col 1: Logo & TERBATAS --}}
            <td style="width: 18%; border: 0.5pt solid #000; text-align: center; vertical-align: top; padding: 0;">
                <div style="padding: 4px 2px 2px 2px;">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" width="90" height="38" alt="KAI">
                    @else
                        <span style="font-size: 18pt; font-weight: 900; color: #1D2A68;">K<span style="color: #ED6B22;">A</span>I</span>
                    @endif
                </div>
                <div style="border-top: 0.5pt solid #000; padding: 2px 0;">
                    <span style="border: 1.5pt solid #FFD700; padding: 0px 6px; color: #FFCC00; font-weight: 900; font-size: 7.5pt; letter-spacing: 1px;">TERBATAS</span>
                </div>
            </td>

            {{-- Col 2: Company Title & Form Name --}}
            <td style="width: 54%; border: 0.5pt solid #000; text-align: center; vertical-align: top; padding: 0;">
                <div style="padding: 5px 2px 2px 2px;">
                    <div style="font-size: 8pt; font-weight: bold; text-transform: uppercase;">
                        {{ $formConfig?->company_name ?? 'PT. KERETA API INDONESIA (PERSERO)' }}
                    </div>
                    <div style="font-size: 7pt; font-weight: bold; margin-top: 1px;">
                        {{ $formConfig?->department_name ?? 'Sistem Informasi' }}
                    </div>
                </div>
                <div style="border-top: 0.5pt solid #000; padding: 3px 0;">
                    <span style="font-size: 8pt; font-weight: 900; letter-spacing: 0.5px;">FORMULIR PC/LAPTOP CHECKING</span>
                </div>
            </td>

            {{-- Col 3: Document Metadata --}}
            <td style="width: 28%; border: 0.5pt solid #000; vertical-align: top; padding: 0;">
                <table style="width: 100%; border-collapse: collapse; font-size: 6.5pt;">
                    <tr>
                        <td style="width: 35%; padding: 2px 4px; border-bottom: 0.5pt solid #000;">Nomor</td>
                        <td style="width: 3%; padding: 2px 0; border-bottom: 0.5pt solid #000; text-align: center;">:</td>
                        <td style="width: 62%; padding: 2px 4px; border-bottom: 0.5pt solid #000;">{{ $formConfig?->form_number ?? 'FR.SM/TI/017.002/10-2020' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 4px; border-bottom: 0.5pt solid #000;">Tanggal Terbit</td>
                        <td style="padding: 2px 0; border-bottom: 0.5pt solid #000; text-align: center;">:</td>
                        <td style="padding: 2px 4px; border-bottom: 0.5pt solid #000;">{{ $formConfig?->publication_date ? $formConfig->publication_date->translatedFormat('d F Y') : '12 Oktober 2020' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 4px; border-bottom: 0.5pt solid #000;">Status Revisi</td>
                        <td style="padding: 2px 0; border-bottom: 0.5pt solid #000; text-align: center;">:</td>
                        <td style="padding: 2px 4px; border-bottom: 0.5pt solid #000;">{{ $formConfig?->revision_status ?? '002-2020' }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 2px 4px;">Halaman</td>
                        <td style="padding: 2px 0; text-align: center;">:</td>
                        <td style="padding: 2px 4px;">{{ $formConfig?->page_label ?? '1 dari 1' }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- ================================================ --}}
    {{-- 2. METADATA                                      --}}
    {{-- ================================================ --}}
    <table style="border: 0.5pt solid #000; margin-bottom: 2px; font-size: 7pt;">
        <tr>
            <td style="width: 75px; padding: 1px 4px; border: 0.5pt solid #000;">No. Ref</td>
            <td style="padding: 1px 4px; border: 0.5pt solid #000;">: {{ $refNumber ?? ($periodName ?? '__/__/____') }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 4px; border: 0.5pt solid #000;">Tanggal</td>
            <td style="padding: 1px 4px; border: 0.5pt solid #000;">: {{ $documentDate ? $documentDate->format('d-m-Y') : now()->format('d-m-Y') }}</td>
        </tr>
        <tr>
            <td style="padding: 1px 4px; border: 0.5pt solid #000;">Business Area</td>
            <td style="padding: 1px 4px; border: 0.5pt solid #000;">: {{ $businessAreaName ?? 'Daop 2 Bandung' }}</td>
        </tr>
    </table>

    <div style="font-size: 7pt; line-height: 1.3; margin-bottom: 3px;">
        <div><strong>Periode Pemeriksaan :</strong> {{ $periodName ?? '-' }}</div>
        <div><strong>Tanggal Pemeriksaan :</strong> {{ $dateRange ?? '-' }}</div>
    </div>

    {{-- ================================================ --}}
    {{-- 3. MAIN SPREADSHEET GRID (17 COLUMNS)            --}}
    {{-- ================================================ --}}
    <table style="width: 100%; border: 0.5pt solid #000; font-size: 6pt;">
        <thead>
            <tr>
                <th rowspan="3" style="width: 2.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">NO</th>
                <th rowspan="3" style="width: 12%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">NAMA PENGGUNA</th>
                <th rowspan="3" style="width: 7%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">UNIT</th>
                <th colspan="12" style="border: 0.5pt solid #000; background: #BFBFBF; text-align: center; font-weight: bold; padding: 1px; line-height: 1.05;">CHECKLIST</th>
                <th colspan="2" style="border: 0.5pt solid #000; background: #BFBFBF; text-align: center; font-weight: bold; padding: 1px; line-height: 1.05;">Verifikasi / Paraf</th>
            </tr>
            <tr>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">NDA<br>(Sudah/Belum)</th>
                <th rowspan="2" style="width: 6%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Login<br>Strong<br>Password<br>(Sudah/Belum)</th>
                <th rowspan="2" style="width: 6%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Screensaver<br>Lock<br>(maks 5<br>menit)<br>(Sudah/Belum)</th>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">* Hak<br>Akses<br>Khusus<br>(Admin /<br>User)</th>
                <th rowspan="2" style="width: 5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Cleardesk<br>(Sudah/<br>Belum)</th>
                <th rowspan="2" style="width: 5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">.mp3,<br>video,<br>etc<br>(Ada/Tidak)</th>
                <th colspan="3" style="border: 0.5pt solid #000; background: #BFBFBF; text-align: center; font-weight: bold; padding: 1px; line-height: 1.05;">Antivirus</th>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">O/S<br>(License /<br>Tidak)</th>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Sinkronisasi<br>NTP<br>Server<br>(Ya/Tidak)</th>
                <th rowspan="2" style="width: 4.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Label PC<br>(Ada/Tidak)</th>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Pemeriksa</th>
                <th rowspan="2" style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Pegawai<br>Ybs</th>
            </tr>
            <tr>
                <th style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Status<br>Install<br>(Sudah/<br>Belum)</th>
                <th style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Status<br>Update<br>(Sudah/<br>Belum)</th>
                <th style="width: 5.5%; border: 0.5pt solid #000; background: #BFBFBF; text-align: center; vertical-align: middle; font-weight: bold; padding: 1px; line-height: 1.05;">Full Scan<br>Auto<br>Schedule<br>(Sudah/Belum)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($inspections as $index => $insp)
                @php
                    $c = $insp->checklist;
                    $unitDisplay = preg_replace('/^Unit\s+/i', '', $insp->employee?->unit?->name ?? '') ?: ($insp->employee?->unit?->name ?? '-');
                @endphp
                <tr>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px; font-weight: bold;">{{ $index + 1 }}</td>
                    <td style="border: 0.5pt solid #000; text-align: left; padding: 2px 2px; height: 12px; font-weight: bold;">{{ $insp->employee?->name ?? '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 2px; height: 12px; line-height: 1.1;">{{ $unitDisplay }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->nda_status ? ucfirst(strtolower($c->nda_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->strong_password_status ? ucfirst(strtolower($c->strong_password_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->screensaver_lock_status ? ucfirst(strtolower($c->screensaver_lock_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ is_object($c?->special_access) ? $c->special_access->label() : ($c?->special_access ? ucfirst(strtolower($c->special_access)) : '-') }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->cleardesk_status ? ucfirst(strtolower($c->cleardesk_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->prohibited_files_status ? ucfirst(strtolower($c->prohibited_files_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->antivirus_install_status ? ucfirst(strtolower($c->antivirus_install_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->antivirus_update_status ? ucfirst(strtolower($c->antivirus_update_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->full_scan_schedule_status ? ucfirst(strtolower($c->full_scan_schedule_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->os_license_status === 'LICENSE' ? 'License' : ($c?->os_license_status ? ucfirst(strtolower($c->os_license_status)) : '-') }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->ntp_sync_status ? ucfirst(strtolower($c->ntp_sync_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $c?->pc_label_status ? ucfirst(strtolower($c->pc_label_status)) : '-' }}</td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px; vertical-align: middle;">
                        @if(!empty($insp->inspector_sig_base64))
                            <img src="{{ $insp->inspector_sig_base64 }}" style="max-height: 13px; max-width: 35px;">
                        @elseif($insp->inspector)
                            <span style="font-size: 5.5pt; font-weight: bold; color: #1e3a8a;">{{ Str::limit($insp->inspector->name, 9, '') }}</span>
                        @else
                            -
                        @endif
                    </td>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px; vertical-align: middle;">
                        @if(!empty($insp->employee_sig_base64))
                            <img src="{{ $insp->employee_sig_base64 }}" style="max-height: 13px; max-width: 35px;">
                        @elseif($insp->employee)
                            <span style="font-size: 5.5pt; font-weight: bold; color: #1e3a8a;">{{ Str::limit($insp->employee->name, 9, '') }}</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" style="border: 0.5pt solid #000; text-align: center; padding: 8px;">Tidak ada data pemeriksaan.</td>
                </tr>
            @endforelse

            {{-- Rows padding up to 10 --}}
            @php
                $currentCount = count($inspections);
            @endphp
            @for($i = $currentCount + 1; $i <= 10; $i++)
                <tr>
                    <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">{{ $i }}</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                    <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                </tr>
            @endfor

            {{-- Row <n> --}}
            <tr>
                <td style="border: 0.5pt solid #000; text-align: center; padding: 2px 1px; height: 12px;">&lt;n&gt;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
                <td style="border: 0.5pt solid #000; padding: 2px 1px; height: 12px;">&nbsp;</td>
            </tr>
        </tbody>
    </table>

    {{-- ================================================ --}}
    {{-- 4. FOOTER: CATATAN & MENGETAHUI                  --}}
    {{-- ================================================ --}}
    <table style="width: 100%; margin-top: 4px; page-break-inside: avoid;">
        <tr>
            {{-- Catatan Box (Left) --}}
            <td style="width: 55%; border: 0.5pt solid #000; padding: 3px 5px; height: 55px; vertical-align: top; font-size: 6.5pt;">
                <div style="font-weight: bold; margin-bottom: 1px;">Catatan :</div>
                <div style="line-height: 1.3;">
                    @if(!empty($notesSummary))
                        {{ $notesSummary }}
                    @else
                        -
                    @endif
                </div>
            </td>

            {{-- Spacer --}}
            <td style="width: 5%;">&nbsp;</td>

            {{-- Signature Area (Right) --}}
            <td style="width: 40%; vertical-align: top; text-align: center; font-size: 7pt;">
                <div style="border-bottom: 0.5pt dashed #000; display: inline-block; padding-bottom: 1px;">
                    {{ $businessAreaName ?? '.......................' }},
                    {{ $documentDate ? $documentDate->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}
                </div>

                <div style="margin-top: 3px; font-weight: bold;">Mengetahui,</div>

                <div style="height: 30px; margin: 2px 0; text-align: center;">
                    @if(!empty($approverSig))
                        <img src="{{ $approverSig }}" style="max-height: 28px; max-width: 110px;">
                    @endif
                </div>

                <div style="border-bottom: 0.5pt dashed #000; display: inline-block; min-width: 130px; font-weight: bold;">
                    {{ $approverName ?? '......................................................' }}
                </div>
                <div style="margin-top: 1px;">
                    NIPP. {{ $approverNipp ?? '.........................' }}
                </div>
            </td>
        </tr>
    </table>

</body>
</html>
