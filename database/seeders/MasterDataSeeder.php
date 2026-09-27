<?php

namespace Database\Seeders;

use App\Enums\ApprovalDecision;
use App\Enums\ApprovalLevel;
use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Enums\InspectionPeriodStatus;
use App\Enums\InspectionStatus;
use App\Enums\NoteType;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionApproval;
use App\Models\InspectionChecklist;
use App\Models\InspectionNote;
use App\Models\InspectionPeriod;
use App\Models\InspectionStatusHistory;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();
        $adminRole = Role::where('name', 'admin')->first();
        $inspectorRole = Role::where('name', 'inspector')->first();

        // 1. Business Area
        $ba = BusinessArea::firstOrCreate(
            ['code' => 'DAOP-2'],
            [
                'name' => 'Daop 2 Bandung',
                'address' => 'Jl. Stasiun Timur No. 1, Kebon Jeruk, Andir, Kota Bandung, Jawa Barat 40181',
                'is_active' => true,
            ]
        );

        // 2. Units
        $unitTi = Unit::firstOrCreate(
            ['business_area_id' => $ba->id, 'code' => 'TI'],
            ['name' => 'Unit Sistem Informasi & Teknologi Informasi', 'is_active' => true]
        );

        $unitSdm = Unit::firstOrCreate(
            ['business_area_id' => $ba->id, 'code' => 'SDM'],
            ['name' => 'Unit Sumber Daya Manusia & Umum', 'is_active' => true]
        );

        $unitKeu = Unit::firstOrCreate(
            ['business_area_id' => $ba->id, 'code' => 'KEU'],
            ['name' => 'Unit Akuntansi & Keuangan', 'is_active' => true]
        );

        $units = [$unitTi, $unitSdm, $unitKeu];

        // 3. Default Users
        $superAdmin = User::updateOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'role_id' => $superAdminRole->id,
                'business_area_id' => null,
                'unit_id' => null,
                'name' => 'Super Administrator',
                'nipp' => 'SA001',
                'password' => Hash::make('password'),
                'phone' => '08110000001',
                'is_active' => true,
            ]
        );

        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'role_id' => $adminRole->id,
                'business_area_id' => $ba->id,
                'unit_id' => $unitTi->id,
                'name' => 'Administrator',
                'nipp' => 'ADM001',
                'password' => Hash::make('password'),
                'phone' => '08110000002',
                'is_active' => true,
            ]
        );

        $inspector = User::updateOrCreate(
            ['email' => 'pemeriksa@example.com'],
            [
                'role_id' => $inspectorRole->id,
                'business_area_id' => $ba->id,
                'unit_id' => $unitTi->id,
                'name' => 'Pemeriksa',
                'nipp' => 'PMR001',
                'password' => Hash::make('password'),
                'phone' => '08110000003',
                'is_active' => true,
            ]
        );

        // 4. Employees (10 employees)
        $employeeData = [
            ['nipp' => 'KAI001', 'name' => 'Budi Santoso', 'email' => 'budi.santoso@example.com', 'position' => 'Senior IT Specialist', 'unit' => $unitTi],
            ['nipp' => 'KAI002', 'name' => 'Siti Nurhaliza', 'email' => 'siti.nurhaliza@example.com', 'position' => 'Network Administrator', 'unit' => $unitTi],
            ['nipp' => 'KAI003', 'name' => 'Agus Priyono', 'email' => 'agus.priyono@example.com', 'position' => 'Database Administrator', 'unit' => $unitTi],
            ['nipp' => 'KAI004', 'name' => 'Dewi Lestari', 'email' => 'dewi.lestari@example.com', 'position' => 'Helpdesk Support', 'unit' => $unitTi],
            ['nipp' => 'KAI005', 'name' => 'Rian Hidayat', 'email' => 'rian.hidayat@example.com', 'position' => 'Staff Kepegawaian', 'unit' => $unitSdm],
            ['nipp' => 'KAI006', 'name' => 'Maya Anggraeni', 'email' => 'maya.anggraeni@example.com', 'position' => 'Staff Pelatihan & Diklat', 'unit' => $unitSdm],
            ['nipp' => 'KAI007', 'name' => 'Hendra Setiawan', 'email' => 'hendra.setiawan@example.com', 'position' => 'Staff Administrasi Umum', 'unit' => $unitSdm],
            ['nipp' => 'KAI008', 'name' => 'Ratna Sari', 'email' => 'ratna.sari@example.com', 'position' => 'Analis Keuangan', 'unit' => $unitKeu],
            ['nipp' => 'KAI009', 'name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@example.com', 'position' => 'Staff Perbendaharaan', 'unit' => $unitKeu],
            ['nipp' => 'KAI010', 'name' => 'Fitri Handayani', 'email' => 'fitri.handayani@example.com', 'position' => 'Staff Akuntansi', 'unit' => $unitKeu],
        ];

        $createdEmployees = [];
        foreach ($employeeData as $emp) {
            $createdEmployees[] = Employee::updateOrCreate(
                ['nipp' => $emp['nipp']],
                [
                    'unit_id' => $emp['unit']->id,
                    'name' => $emp['name'],
                    'email' => $emp['email'],
                    'position' => $emp['position'],
                    'is_active' => true,
                ]
            );
        }

        // 5. Devices (10 devices linked to employees)
        $deviceData = [
            ['asset' => 'AST-KAI-001', 'host' => 'PC-TI-01', 'type' => DeviceType::PC, 'brand' => 'Lenovo', 'model' => 'ThinkCentre M70q', 'serial' => 'SN-LNV-001', 'ip' => '10.2.10.11', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
            ['asset' => 'AST-KAI-002', 'host' => 'NB-TI-02', 'type' => DeviceType::LAPTOP, 'brand' => 'Dell', 'model' => 'Latitude 5430', 'serial' => 'SN-DLL-002', 'ip' => '10.2.10.12', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
            ['asset' => 'AST-KAI-003', 'host' => 'PC-TI-03', 'type' => DeviceType::PC, 'brand' => 'HP', 'model' => 'ProDesk 400 G7', 'serial' => 'SN-HP-003', 'ip' => '10.2.10.13', 'os' => 'Windows 10 Pro', 'ver' => '22H2'],
            ['asset' => 'AST-KAI-004', 'host' => 'NB-TI-04', 'type' => DeviceType::LAPTOP, 'brand' => 'Lenovo', 'model' => 'ThinkPad L14 Gen 3', 'serial' => 'SN-LNV-004', 'ip' => '10.2.10.14', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
            ['asset' => 'AST-KAI-005', 'host' => 'PC-SDM-01', 'type' => DeviceType::PC, 'brand' => 'HP', 'model' => 'EliteDesk 800 G6', 'serial' => 'SN-HP-005', 'ip' => '10.2.20.15', 'os' => 'Windows 10 Pro', 'ver' => '22H2'],
            ['asset' => 'AST-KAI-006', 'host' => 'NB-SDM-02', 'type' => DeviceType::LAPTOP, 'brand' => 'Asus', 'model' => 'ExpertBook B1', 'serial' => 'SN-ASU-006', 'ip' => '10.2.20.16', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
            ['asset' => 'AST-KAI-007', 'host' => 'PC-SDM-03', 'type' => DeviceType::PC, 'brand' => 'Dell', 'model' => 'OptiPlex 3080', 'serial' => 'SN-DLL-007', 'ip' => '10.2.20.17', 'os' => 'Windows 10 Pro', 'ver' => '22H2'],
            ['asset' => 'AST-KAI-008', 'host' => 'NB-KEU-01', 'type' => DeviceType::LAPTOP, 'brand' => 'Lenovo', 'model' => 'ThinkPad E14 Gen 4', 'serial' => 'SN-LNV-008', 'ip' => '10.2.30.18', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
            ['asset' => 'AST-KAI-009', 'host' => 'PC-KEU-02', 'type' => DeviceType::PC, 'brand' => 'HP', 'model' => 'ProDesk 400 G7', 'serial' => 'SN-HP-009', 'ip' => '10.2.30.19', 'os' => 'Windows 10 Pro', 'ver' => '22H2'],
            ['asset' => 'AST-KAI-010', 'host' => 'NB-KEU-03', 'type' => DeviceType::LAPTOP, 'brand' => 'Dell', 'model' => 'Vostro 3420', 'serial' => 'SN-DLL-010', 'ip' => '10.2.30.20', 'os' => 'Windows 11 Pro', 'ver' => '23H2'],
        ];

        $createdDevices = [];
        foreach ($deviceData as $idx => $d) {
            $createdDevices[] = Device::updateOrCreate(
                ['asset_number' => $d['asset']],
                [
                    'employee_id' => $createdEmployees[$idx]->id,
                    'hostname' => $d['host'],
                    'device_type' => $d['type'],
                    'brand' => $d['brand'],
                    'model' => $d['model'],
                    'serial_number' => $d['serial'],
                    'ip_address' => $d['ip'],
                    'operating_system' => $d['os'],
                    'os_version' => $d['ver'],
                    'device_status' => DeviceStatus::ACTIVE,
                    'description' => 'Perangkat operasional kerja ' . $createdEmployees[$idx]->name,
                ]
            );
        }

        // 6. Inspection Period (1 Active Period)
        $period = InspectionPeriod::firstOrCreate(
            ['name' => 'Pemeriksaan Triwulan III Tahun 2026', 'business_area_id' => $ba->id],
            [
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->endOfMonth()->toDateString(),
                'status' => InspectionPeriodStatus::ACTIVE,
                'created_by' => $admin->id,
            ]
        );

        // 7. Inspections in various statuses
        $statusPlan = [
            0 => InspectionStatus::ASSIGNED,
            1 => InspectionStatus::IN_PROGRESS,
            2 => InspectionStatus::PENDING_ADMIN_VERIFICATION,
            3 => InspectionStatus::REVISION_REQUIRED,
            4 => InspectionStatus::RESUBMITTED,
            5 => InspectionStatus::ADMIN_VERIFIED,
            6 => InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
            7 => InspectionStatus::APPROVED,
            8 => InspectionStatus::LOCKED,
            9 => InspectionStatus::ARCHIVED,
        ];

        $year = now()->format('Y');
        $monthRoman = 'IX'; // September

        foreach ($statusPlan as $idx => $status) {
            $device = $createdDevices[$idx];
            $employee = $createdEmployees[$idx];
            $seq = str_pad($idx + 1, 3, '0', STR_PAD_LEFT);
            $refNumber = "{$seq}/TI/PC-CHECK/{$monthRoman}/{$year}";

            $inspection = Inspection::updateOrCreate(
                [
                    'inspection_period_id' => $period->id,
                    'device_id' => $device->id,
                ],
                [
                    'public_id' => (string) Str::uuid(),
                    'employee_id' => $employee->id,
                    'inspector_id' => $inspector->id,
                    'reference_number' => $refNumber,
                    'document_date' => now()->toDateString(),
                    'inspection_date' => $status !== InspectionStatus::ASSIGNED ? now()->toDateString() : null,
                    'status' => $status,
                    'general_notes' => 'Pemeriksaan rutin keamanan perangkat di lingkungan kerja Daop 2.',
                    'submitted_at' => in_array($status, [
                        InspectionStatus::PENDING_ADMIN_VERIFICATION,
                        InspectionStatus::REVISION_REQUIRED,
                        InspectionStatus::RESUBMITTED,
                        InspectionStatus::ADMIN_VERIFIED,
                        InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                        InspectionStatus::APPROVED,
                        InspectionStatus::LOCKED,
                        InspectionStatus::ARCHIVED,
                    ]) ? now()->subDays(3) : null,
                    'admin_verified_at' => in_array($status, [
                        InspectionStatus::ADMIN_VERIFIED,
                        InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                        InspectionStatus::APPROVED,
                        InspectionStatus::LOCKED,
                        InspectionStatus::ARCHIVED,
                    ]) ? now()->subDays(2) : null,
                    'approved_at' => in_array($status, [
                        InspectionStatus::APPROVED,
                        InspectionStatus::LOCKED,
                        InspectionStatus::ARCHIVED,
                    ]) ? now()->subDay() : null,
                    'locked_at' => in_array($status, [
                        InspectionStatus::LOCKED,
                        InspectionStatus::ARCHIVED,
                    ]) ? now()->subHours(12) : null,
                    'completed_at' => in_array($status, [
                        InspectionStatus::APPROVED,
                        InspectionStatus::LOCKED,
                        InspectionStatus::ARCHIVED,
                    ]) ? now()->subDay() : null,
                ]
            );

            // Checklist
            if ($status !== InspectionStatus::ASSIGNED) {
                $hasFinding = ($idx % 2 === 1); // Alternating findings
                InspectionChecklist::updateOrCreate(
                    ['inspection_id' => $inspection->id],
                    [
                        'nda_status' => 'SUDAH',
                        'strong_password_status' => $hasFinding ? 'BELUM' : 'SUDAH',
                        'screensaver_lock_status' => 'SUDAH',
                        'special_access' => 'USER',
                        'cleardesk_status' => 'SUDAH',
                        'prohibited_files_status' => 'TIDAK',
                        'antivirus_install_status' => 'SUDAH',
                        'antivirus_update_status' => $hasFinding ? 'BELUM' : 'SUDAH',
                        'full_scan_schedule_status' => 'SUDAH',
                        'os_license_status' => 'LICENSE',
                        'ntp_sync_status' => 'YA',
                        'pc_label_status' => 'ADA',
                        'finding_summary' => $hasFinding ? 'Password login masih standar dan update antivirus belum versi terbaru.' : null,
                    ]
                );
            }

            // Initial Status History
            InspectionStatusHistory::create([
                'inspection_id' => $inspection->id,
                'changed_by' => $admin->id,
                'from_status' => null,
                'to_status' => InspectionStatus::ASSIGNED,
                'action' => 'assign',
                'reason' => 'Penugasan pemeriksaan periode ' . $period->name,
                'created_at' => now()->subDays(5),
            ]);

            if ($status !== InspectionStatus::ASSIGNED) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $inspector->id,
                    'from_status' => InspectionStatus::ASSIGNED,
                    'to_status' => InspectionStatus::IN_PROGRESS,
                    'action' => 'start',
                    'reason' => 'Pemeriksa memulai pemeriksaan perangkat fisik.',
                    'created_at' => now()->subDays(4),
                ]);
            }

            if (in_array($status, [
                InspectionStatus::PENDING_ADMIN_VERIFICATION,
                InspectionStatus::REVISION_REQUIRED,
                InspectionStatus::RESUBMITTED,
                InspectionStatus::ADMIN_VERIFIED,
                InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED,
            ])) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $inspector->id,
                    'from_status' => InspectionStatus::IN_PROGRESS,
                    'to_status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
                    'action' => 'submit',
                    'reason' => 'Pemeriksaan selesai diisi dan dikirim kepada Admin.',
                    'created_at' => now()->subDays(3),
                ]);

                InspectionApproval::create([
                    'inspection_id' => $inspection->id,
                    'user_id' => $inspector->id,
                    'approval_level' => ApprovalLevel::INSPECTOR,
                    'decision' => ApprovalDecision::SUBMITTED,
                    'comment' => 'Checklist telah diisi sesuai kondisi fisik perangkat.',
                    'action_at' => now()->subDays(3),
                ]);
            }

            if ($status === InspectionStatus::REVISION_REQUIRED) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $admin->id,
                    'from_status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
                    'to_status' => InspectionStatus::REVISION_REQUIRED,
                    'action' => 'request_revision',
                    'reason' => 'Mohon sertakan screenshot bukti update antivirus dan klarifikasi password.',
                    'created_at' => now()->subDays(2),
                ]);

                InspectionNote::create([
                    'inspection_id' => $inspection->id,
                    'user_id' => $admin->id,
                    'note_type' => NoteType::ADMIN_REVISION,
                    'note' => 'Mohon sertakan screenshot bukti update antivirus dan klarifikasi password.',
                    'is_internal' => false,
                ]);
            }

            if (in_array($status, [
                InspectionStatus::ADMIN_VERIFIED,
                InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED,
            ])) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $admin->id,
                    'from_status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
                    'to_status' => InspectionStatus::ADMIN_VERIFIED,
                    'action' => 'verify',
                    'reason' => 'Pemeriksaan telah diverifikasi lengkap oleh Admin.',
                    'created_at' => now()->subDays(2),
                ]);

                InspectionApproval::create([
                    'inspection_id' => $inspection->id,
                    'user_id' => $admin->id,
                    'approval_level' => ApprovalLevel::ADMIN,
                    'decision' => ApprovalDecision::VERIFIED,
                    'comment' => 'Pemeriksaan telah diverifikasi sesuai prosedur operasional standar.',
                    'action_at' => now()->subDays(2),
                ]);
            }

            if (in_array($status, [
                InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED,
            ])) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $admin->id,
                    'from_status' => InspectionStatus::ADMIN_VERIFIED,
                    'to_status' => InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                    'action' => 'submit_approval',
                    'reason' => 'Diajukan ke Super Admin untuk persetujuan akhir.',
                    'created_at' => now()->subDays(1),
                ]);
            }

            if (in_array($status, [
                InspectionStatus::APPROVED,
                InspectionStatus::LOCKED,
                InspectionStatus::ARCHIVED,
            ])) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $superAdmin->id,
                    'from_status' => InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
                    'to_status' => InspectionStatus::APPROVED,
                    'action' => 'approve',
                    'reason' => 'Disetujui secara final oleh Super Administrator.',
                    'created_at' => now()->subDay(),
                ]);

                InspectionApproval::create([
                    'inspection_id' => $inspection->id,
                    'user_id' => $superAdmin->id,
                    'approval_level' => ApprovalLevel::SUPER_ADMIN,
                    'decision' => ApprovalDecision::APPROVED,
                    'comment' => 'Disetujui. Laporan final siap diterbitkan.',
                    'action_at' => now()->subDay(),
                ]);
            }

            if (in_array($status, [InspectionStatus::LOCKED, InspectionStatus::ARCHIVED])) {
                InspectionStatusHistory::create([
                    'inspection_id' => $inspection->id,
                    'changed_by' => $superAdmin->id,
                    'from_status' => InspectionStatus::APPROVED,
                    'to_status' => InspectionStatus::LOCKED,
                    'action' => 'lock',
                    'reason' => 'Data dikunci untuk keperluan audit dan pengarsipan resmi.',
                    'created_at' => now()->subHours(12),
                ]);
            }
        }
    }
}
