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
        $response->assertSee('Pegawai');
        $response->assertSee('Unit');
        $response->assertDontSee('Pegawai & Unit');
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

    public function test_period_index_page_only_shows_zip_download_button(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.inspection-periods.index'));
        $response->assertStatus(200);

        // ZIP button and Delete button should be present
        $response->assertSee(route('reports.period.zip', $this->period));
        $response->assertSee(route('admin.inspection-periods.destroy', $this->period));

        // PDF and Excel export buttons on inspection-periods should NOT be present
        $response->assertDontSee(route('reports.period.pdf', $this->period));
        $response->assertDontSee(route('reports.period.excel', $this->period));
    }

    public function test_period_zip_structure_has_consolidated_pdf_and_single_recap_pdf_per_unit_folder(): void
    {
        // Create another inspection in the same unit
        $device2 = Device::factory()->create(['employee_id' => $this->inspection->employee_id]);
        $inspection2 = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $device2->id,
            'employee_id' => $this->inspection->employee_id,
            'inspector_id' => $this->superAdmin->id,
            'assigned_by' => $this->superAdmin->id,
            'reference_number' => '0002/TI/PC-CHECK/IX/2026',
            'document_date' => now(),
            'status' => InspectionStatus::APPROVED,
            'approved_at' => now(),
        ]);
        InspectionChecklist::create([
            'inspection_id' => $inspection2->id,
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

        $reportService = app(\App\Services\ReportService::class);
        $zipPath = $reportService->generatePeriodZip($this->period);

        $this->assertFileExists($zipPath);

        $zip = new \ZipArchive();
        $this->assertTrue($zip->open($zipPath) === true);

        $fileNames = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileNames[] = $zip->getNameIndex($i);
        }
        $zip->close();
        @unlink($zipPath);

        // 1. Root MUST have consolidated recap PDF for all units
        $hasRootRecap = collect($fileNames)->contains(fn($name) => str_starts_with($name, '00_REKAPITULASI_PEMERIKSAAN_') && str_ends_with($name, '.pdf'));
        $this->assertTrue($hasRootRecap, 'ZIP must contain consolidated recap in root.');

        // 2. Unit folder MUST have exactly 1 file: REKAP_PEMERIKSAAN_...
        $unitFiles = collect($fileNames)->filter(fn($name) => str_contains($name, '/'));
        $this->assertCount(1, $unitFiles, 'Unit folder must only contain 1 recap PDF even with 2 inspections in that unit.');

        $unitFileName = $unitFiles->first();
        $this->assertTrue(str_starts_with($unitFileName, 'unit_report_unit/REKAP_PEMERIKSAAN_'));
        $this->assertTrue(str_ends_with($unitFileName, '.pdf'));

        // 3. NO individual inspection files in ZIP
        $this->assertFalse(collect($fileNames)->contains(fn($name) => str_contains($name, 'NO_ASSET') || str_contains($name, '0001_TI_PC-CHECK')));
    }

    public function test_deleting_period_cascades_and_removes_all_inspections_and_report_data(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $sigPath = 'signatures/test_emp_sig.png';
        \Illuminate\Support\Facades\Storage::disk('public')->put($sigPath, 'fake-sig-content');

        $this->inspection->update(['employee_signature_path' => $sigPath]);

        \App\Models\InspectionNote::create([
            'inspection_id' => $this->inspection->id,
            'user_id' => $this->superAdmin->id,
            'note' => 'Catatan uji coba',
        ]);

        $this->assertDatabaseHas('inspections', ['id' => $this->inspection->id]);
        $this->assertDatabaseHas('inspection_periods', ['id' => $this->period->id]);
        $this->assertTrue(\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath));

        // Ensure visible in reports index
        $reportsRes = $this->actingAs($this->superAdmin)->get(route('reports.index'));
        $reportsRes->assertSee($this->inspection->reference_number);

        // Delete the period
        $deleteRes = $this->actingAs($this->superAdmin)->delete(route('admin.inspection-periods.destroy', $this->period));
        $deleteRes->assertRedirect(route('admin.inspection-periods.index'));

        // Assert period and inspection completely deleted
        $this->assertDatabaseMissing('inspection_periods', ['id' => $this->period->id]);
        $this->assertDatabaseMissing('inspections', ['id' => $this->inspection->id]);
        $this->assertNull(Inspection::withTrashed()->find($this->inspection->id));
        $this->assertDatabaseMissing('inspection_checklists', ['inspection_id' => $this->inspection->id]);
        $this->assertDatabaseMissing('inspection_notes', ['inspection_id' => $this->inspection->id]);

        // Assert file deleted from storage
        $this->assertFalse(\Illuminate\Support\Facades\Storage::disk('public')->exists($sigPath));

        // Ensure no longer visible in reports
        $reportsAfterRes = $this->actingAs($this->superAdmin)->get(route('reports.index'));
        $reportsAfterRes->assertDontSee($this->inspection->reference_number);
    }

    public function test_period_recap_pdf_does_not_contain_unit_kerja_metadata_and_uses_full_unit_name(): void
    {
        $reportService = app(\App\Services\ReportService::class);
        $data = $reportService->prepareRecapReportData(collect([$this->inspection]), $this->period, $this->inspection->employee->unit->name);
        $renderedView = view('reports.pdf.period_recap', $data)->render();

        // 1. Must NOT contain "Unit Kerja :"
        $this->assertStringNotContainsString('Unit Kerja :', $renderedView);

        // 2. Must contain full unit name in the table
        $expectedUnit = preg_replace('/^Unit\s+/i', '', $this->inspection->employee->unit->name);
        $this->assertStringContainsString($expectedUnit, $renderedView);
    }
}
