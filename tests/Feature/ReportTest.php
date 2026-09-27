<?php

namespace Tests\Feature;

use App\Enums\InspectionStatus;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionChecklist;
use App\Models\InspectionPeriod;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\TestCase;

class ReportTest extends TestCase
{
    protected User $superAdmin;
    protected Inspection $inspection;
    protected InspectionPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin', 'level' => 1]);
        $ba = BusinessArea::firstOrCreate(['code' => 'REP_BA'], ['name' => 'Report Area', 'is_active' => true]);
        $unit = Unit::firstOrCreate(['business_area_id' => $ba->id, 'code' => 'REP_UNIT'], ['name' => 'Report Unit', 'is_active' => true]);

        $this->superAdmin = User::factory()->create([
            'role_id' => $role->id,
            'business_area_id' => $ba->id,
        ]);

        $employee = Employee::factory()->create([
            'unit_id' => $unit->id,
        ]);

        $device = Device::factory()->create([
            'employee_id' => $employee->id,
        ]);

        $this->period = InspectionPeriod::create([
            'business_area_id' => $ba->id,
            'created_by' => $this->superAdmin->id,
            'name' => 'Periode Laporan Test',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);

        $this->inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $device->id,
            'employee_id' => $employee->id,
            'inspector_id' => $this->superAdmin->id,
            'assigned_by' => $this->superAdmin->id,
            'reference_number' => '0001/TI/PC-CHECK/IX/2026',
            'document_date' => now(),
            'status' => InspectionStatus::APPROVED,
            'approved_at' => now(),
        ]);

        InspectionChecklist::create([
            'inspection_id' => $this->inspection->id,
            'nda_status' => 'SUDAH',
            'strong_password_status' => 'SUDAH',
            'screensaver_lock_status' => 'SUDAH',
            'cleardesk_status' => 'SUDAH',
            'prohibited_files_status' => 'TIDAK',
            'antivirus_install_status' => 'SUDAH',
            'antivirus_update_status' => 'SUDAH',
            'full_scan_schedule_status' => 'SUDAH',
            'os_license_status' => 'LICENSE',
            'ntp_sync_status' => 'YA',
            'pc_label_status' => 'ADA',
            'special_access' => 'USER',
            'finding_summary' => 'Sesuai standar',
        ]);
    }

    public function test_can_view_reports_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/reports');
        $response->assertStatus(200);
        $response->assertSee('Pusat Laporan Pemeriksaan');
    }

    public function test_can_preview_printable_form(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/preview");
        $response->assertStatus(200);
        $response->assertSee('FR.SM/TI/017.002/10-2020');
        $response->assertSee($this->inspection->reference_number);
    }

    public function test_can_generate_pdf_report(): void
    {
        $response = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/pdf");
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_can_export_inspections_excel(): void
    {
        $response = $this->actingAs($this->superAdmin)->get('/reports/excel');
        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('Content-Disposition') ?? '', '.xlsx'));
    }

    public function test_preview_displays_approver_signature_when_available(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('signature.png');
        $path = $file->store('signatures', 'public');
        $this->superAdmin->update(['signature_path' => $path]);

        \App\Models\InspectionApproval::create([
            'inspection_id' => $this->inspection->id,
            'user_id' => $this->superAdmin->id,
            'approval_level' => \App\Enums\ApprovalLevel::SUPER_ADMIN,
            'decision' => \App\Enums\ApprovalDecision::APPROVED,
            'comment' => 'Disetujui',
            'action_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/preview");
        $response->assertStatus(200);
        $response->assertDontSee('DISETUJUI SECARA RESMI');
        $response->assertSee('Tanda Tangan Mengetahui');
    }

    public function test_preview_does_not_display_signature_when_manual_approval(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('signature.png');
        $path = $file->store('signatures', 'public');
        $this->superAdmin->update(['signature_path' => $path]);

        \App\Models\InspectionApproval::create([
            'inspection_id' => $this->inspection->id,
            'user_id' => $this->superAdmin->id,
            'approval_level' => \App\Enums\ApprovalLevel::SUPER_ADMIN,
            'decision' => \App\Enums\ApprovalDecision::APPROVED,
            'signature_type' => 'manual',
            'signature_snapshot' => null,
            'comment' => 'Disetujui secara manual',
            'action_at' => now(),
        ]);

        $response = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/preview");
        $response->assertStatus(200);
        $response->assertDontSee('Tanda Tangan Mengetahui');
    }

    public function test_preview_and_pdf_render_drawn_employee_signature(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->image('emp_sig.png', 100, 40);
        $path = $file->store('signatures/employees', 'public');
        $this->inspection->update(['employee_signature_path' => $path]);

        // Preview assert
        $response = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/preview");
        $response->assertStatus(200);
        $response->assertSee('data:image/png;base64,');

        // PDF assert
        $pdfResponse = $this->actingAs($this->superAdmin)->get("/reports/{$this->inspection->public_id}/pdf");
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
