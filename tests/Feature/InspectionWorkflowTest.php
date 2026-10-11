<?php

namespace Tests\Feature;

use App\Enums\InspectionStatus;
use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use App\Services\InspectionService;
use App\Services\InspectionStatusService;
use App\Services\ReferenceNumberService;
use Tests\TestCase;

class InspectionWorkflowTest extends TestCase
{
    protected Role $superAdminRole;
    protected Role $adminRole;
    protected Role $inspectorRole;

    protected User $superAdmin;
    protected User $admin;
    protected User $inspector;
    protected BusinessArea $businessArea;
    protected Unit $unit;
    protected Employee $employee;
    protected Device $device;
    protected InspectionPeriod $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin', 'level' => 1]);
        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Unit', 'level' => 2]);
        $this->inspectorRole = Role::firstOrCreate(['name' => 'inspector'], ['display_name' => 'Pemeriksa', 'level' => 3]);

        $this->businessArea = BusinessArea::firstOrCreate(['code' => 'WF_TEST_BA'], ['name' => 'Workflow Area Test', 'is_active' => true]);
        $this->unit = Unit::firstOrCreate(['business_area_id' => $this->businessArea->id, 'code' => 'WF_UNIT'], ['name' => 'Workflow Unit Test', 'is_active' => true]);

        $this->superAdmin = User::factory()->create([
            'role_id' => $this->superAdminRole->id,
            'business_area_id' => $this->businessArea->id,
        ]);

        $this->admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'business_area_id' => $this->businessArea->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->inspector = User::factory()->create([
            'role_id' => $this->inspectorRole->id,
            'business_area_id' => $this->businessArea->id,
            'unit_id' => $this->unit->id,
        ]);

        $this->employee = Employee::factory()->create([
            'unit_id' => $this->unit->id,
        ]);

        $this->device = Device::factory()->create([
            'employee_id' => $this->employee->id,
        ]);

        $this->period = InspectionPeriod::create([
            'business_area_id' => $this->businessArea->id,
            'created_by' => $this->admin->id,
            'name' => 'Periode Uji Workflow',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
            'is_active' => true,
        ]);
    }

    public function test_full_inspection_lifecycle(): void
    {
        // 1. Batch Assign
        $inspectionService = app(InspectionService::class);
        $inspections = $inspectionService->batchAssign(
            $this->period->id,
            $this->inspector->id,
            now()->format('Y-m-d'),
            null,
            [$this->device->id],
            $this->admin
        );

        $this->assertCount(1, $inspections);
        $inspection = $inspections[0];
        $this->assertEquals(InspectionStatus::ASSIGNED, $inspection->status);

        // 2. Inspector starts inspection
        $responseStart = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/start");
        $responseStart->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::IN_PROGRESS, $inspection->status);

        // 3. Inspector saves draft checklist
        $checklistData = [
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
            'finding_summary' => 'Pemeriksaan lengkap tidak ada temuan.',
        ];

        $responseDraft = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/save-draft", $checklistData);
        $responseDraft->assertSessionHas('success');
        $this->assertDatabaseHas('inspection_checklists', [
            'inspection_id' => $inspection->id,
            'nda_status' => 'SUDAH',
        ]);

        // 4. Inspector submits inspection to Admin
        $responseSubmit = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/submit", array_merge($checklistData, [
            'inspection_date' => now()->format('Y-m-d'),
            'statement' => '1',
            'comment' => 'Siap diverifikasi',
        ]));
        $responseSubmit->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_ADMIN_VERIFICATION, $inspection->status);

        // 5. Admin requests revision
        $responseRev = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/request-revision", [
            'reason' => 'Mohon sertakan screenshot antivirus',
        ]);
        $responseRev->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::REVISION_REQUIRED, $inspection->status);

        // 6. Inspector resubmits
        $responseResubmit = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/resubmit", array_merge($checklistData, [
            'notes' => 'Screenshot telah diperbarui',
        ]));
        $responseResubmit->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::RESUBMITTED, $inspection->status);

        // 7. Admin verifies resubmitted inspection
        $responseVerify = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/verify", [
            'comment' => 'Perbaikan disetujui Admin',
        ]);
        $responseVerify->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::ADMIN_VERIFIED, $inspection->status);

        // 8. Admin submits for Super Admin approval
        $responseApproveAdmin = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/submit-approval", [
            'comment' => 'Diverifikasi dan diteruskan ke Super Admin',
        ]);
        $responseApproveAdmin->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $inspection->status);

        // 8. Super Admin approves
        $responseSA = $this->actingAs($this->superAdmin)->post("/super-admin/inspections/{$inspection->public_id}/approval", [
            'action' => 'approve',
            'comment' => 'Disetujui secara resmi',
        ]);
        $responseSA->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::APPROVED, $inspection->status);
        $this->assertNotNull($inspection->approved_at);

        // 9. Super Admin locks
        $responseLock = $this->actingAs($this->superAdmin)->post("/super-admin/inspections/{$inspection->public_id}/lock");
        $responseLock->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::LOCKED, $inspection->status);

        // 10. Super Admin unlocks
        $responseUnlock = $this->actingAs($this->superAdmin)->post("/super-admin/inspections/{$inspection->public_id}/unlock", [
            'reason' => 'Perbaikan data aset',
        ]);
        $responseUnlock->assertRedirect();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::ADMIN_REVISION_REQUIRED, $inspection->status);
    }

    public function test_super_admin_can_approve_with_manual_signature(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/MANUAL/001',
            'document_date' => now(),
            'status' => InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
        ]);

        $response = $this->actingAs($this->superAdmin)->post("/super-admin/inspections/{$inspection->public_id}/approval", [
            'action' => 'approve_manual',
            'signature_type' => 'manual',
            'comment' => 'Disetujui secara manual',
        ]);

        $response->assertRedirect(route('super-admin.inspections.approved'));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::APPROVED, $inspection->status);

        $approval = $inspection->approvals()->where('decision', \App\Enums\ApprovalDecision::APPROVED)->first();
        $this->assertNotNull($approval);
        $this->assertEquals('manual', $approval->signature_type);
        $this->assertNull($approval->signature_snapshot);
    }

    public function test_inspector_can_save_employee_signature_via_ajax(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'reference_number' => 'TEST/EMPSIG/001',
            'document_date' => now(),
            'status' => InspectionStatus::IN_PROGRESS,
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($this->inspector)->postJson("/inspector/tasks/{$inspection->public_id}/employee-signature", [
            'signature_data' => $fakeSignature,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $inspection->refresh();
        $this->assertNotNull($inspection->employee_signature_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($inspection->employee_signature_path);

        $this->employee->refresh();
        $this->assertEquals($inspection->employee_signature_path, $this->employee->signature_path);
    }

    public function test_inspector_checklist_saves_employee_signature_on_draft(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'reference_number' => 'TEST/DRAFT/001',
            'document_date' => now(),
            'status' => InspectionStatus::IN_PROGRESS,
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

        $response = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/save-draft", [
            'inspection_date' => now()->toDateString(),
            'nda_status' => 'SUDAH',
            'employee_signature_data' => $fakeSignature,
        ]);

        $response->assertRedirect();
        $inspection->refresh();
        $this->assertNotNull($inspection->employee_signature_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($inspection->employee_signature_path);
    }

    public function test_inspector_can_resubmit_inspection_without_validation_errors(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'reference_number' => 'TEST/RESUBMIT/001',
            'document_date' => now(),
            'status' => InspectionStatus::REVISION_REQUIRED,
        ]);

        $payload = [
            'notes' => 'Catatan perbaikan telah disesuaikan dengan instruksi admin.',
            'nda_status' => 'SUDAH',
            'strong_password_status' => 'SUDAH',
            'screensaver_lock_status' => 'SUDAH',
            'special_access' => 'LOCAL_ADMIN', // Tests auto-normalization to ADMIN
            'cleardesk_status' => 'SUDAH',
            'prohibited_files_status' => 'TIDAK_ADA', // Tests auto-normalization to TIDAK
            'antivirus_install_status' => 'SUDAH',
            'antivirus_update_status' => 'SUDAH',
            'full_scan_schedule_status' => 'SUDAH',
            'os_license_status' => 'YA', // Tests auto-normalization to LICENSE
            'ntp_sync_status' => 'YA',
            'pc_label_status' => 'YA', // Tests auto-normalization to ADA
        ];

        $response = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/resubmit", $payload);
        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $inspection->refresh();
        $this->assertEquals(InspectionStatus::RESUBMITTED, $inspection->status);
    }

    public function test_admin_can_verify_and_submit_to_super_admin_in_one_step(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/ONE-STEP/001',
            'document_date' => now(),
            'status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/submit-approval", [
            'comment' => 'Diverifikasi langsung diteruskan ke Super Admin',
        ]);

        $response->assertRedirect(route('admin.verifications.index'));
        $response->assertSessionHas('success');

        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $inspection->status);
        $this->assertNotNull($inspection->admin_verified_at);

        $adminApproval = $inspection->approvals()
            ->where('approval_level', \App\Enums\ApprovalLevel::ADMIN)
            ->where('decision', \App\Enums\ApprovalDecision::VERIFIED)
            ->first();
        $this->assertNotNull($adminApproval);

        // Check transition histories contain both verify and submit_approval
        $transitions = $inspection->statusHistories()->pluck('to_status')->toArray();
        $this->assertContains(InspectionStatus::ADMIN_VERIFIED, $transitions);
        $this->assertContains(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $transitions);
    }

    public function test_admin_can_verify_and_submit_resubmitted_inspection_in_one_step(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/RESUBMIT-ONE-STEP/001',
            'document_date' => now(),
            'status' => InspectionStatus::RESUBMITTED,
        ]);

        $response = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/submit-approval", [
            'comment' => 'Revisi disetujui dan langsung diajukan ke Super Admin',
        ]);

        $response->assertRedirect(route('admin.verifications.index'));
        $response->assertSessionHas('success');

        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $inspection->status);
        $this->assertNotNull($inspection->admin_verified_at);

        $adminApproval = $inspection->approvals()
            ->where('approval_level', \App\Enums\ApprovalLevel::ADMIN)
            ->where('decision', \App\Enums\ApprovalDecision::VERIFIED)
            ->first();
        $this->assertNotNull($adminApproval);

        $transitions = $inspection->statusHistories()->pluck('to_status')->toArray();
        $this->assertContains(InspectionStatus::ADMIN_VERIFIED, $transitions);
        $this->assertContains(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $transitions);
    }

    public function test_admin_revision_message_appears_on_inspector_revision_page(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/REV-MSG/001',
            'document_date' => now(),
            'status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
        ]);

        // Admin requests revision with a custom message 'halooo'
        $customMessage = 'halooo';
        $response = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/request-revision", [
            'reason' => $customMessage,
        ]);

        $response->assertRedirect(route('admin.verifications.index'));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::REVISION_REQUIRED, $inspection->status);

        // Inspector opens revision page
        $revPageResponse = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}/revision");
        $revPageResponse->assertOk();
        $revPageResponse->assertSee($customMessage);
        $revPageResponse->assertDontSee('Mohon periksa dan lengkapi kembali checklist pemeriksaan serta bukti lampiran yang diminta.');

        // Inspector opens show page
        $showPageResponse = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}");
        $showPageResponse->assertOk();
        $showPageResponse->assertSee($customMessage);
    }

    public function test_inspector_item_notes_and_resubmit_notes_appear_on_admin_and_super_admin_pages(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/ITEM-NOTES/001',
            'document_date' => now(),
            'status' => InspectionStatus::REVISION_REQUIRED,
        ]);

        // Inspector resubmits with item notes including 'password123'
        $resubmitResponse = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/resubmit", [
            'notes' => 'Catatan perbaikan komprehensif dari pemeriksa',
            'nda_status' => 'SUDAH',
            'nda_notes' => 'nda-berkas-lengkap',
            'strong_password_status' => 'SUDAH',
            'strong_password_notes' => 'password123',
            'screensaver_lock_status' => 'SUDAH',
            'screensaver_lock_notes' => 'timeout-5-menit',
            'special_access' => 'USER',
            'cleardesk_status' => 'SUDAH',
            'prohibited_files_status' => 'TIDAK',
            'antivirus_install_status' => 'SUDAH',
            'antivirus_name' => 'Trend Micro Apex One',
            'antivirus_update_status' => 'SUDAH',
            'full_scan_schedule_status' => 'SUDAH',
            'os_license_status' => 'LICENSE',
            'ntp_sync_status' => 'YA',
            'pc_label_status' => 'ADA',
        ]);

        $resubmitResponse->assertRedirect(route('inspector.tasks.show', $inspection));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::RESUBMITTED, $inspection->status);

        // Verify database checklist values
        $this->assertEquals('password123', $inspection->checklist->strong_password_notes);
        $this->assertEquals('nda-berkas-lengkap', $inspection->checklist->nda_notes);

        // Admin verification page should display 'password123' and resubmit notes
        $adminView = $this->actingAs($this->admin)->get("/admin/verifications/{$inspection->public_id}");
        $adminView->assertOk();
        $adminView->assertSee('password123');
        $adminView->assertSee('nda-berkas-lengkap');
        $adminView->assertSee('Catatan perbaikan komprehensif dari pemeriksa');

        // Admin verifies and submits to Super Admin
        $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/submit-approval", [
            'comment' => 'Diteruskan ke Super Admin',
        ]);

        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $inspection->status);

        // Super Admin approval page should display 'password123' and notes history
        $superAdminView = $this->actingAs($this->superAdmin)->get("/super-admin/inspections/{$inspection->public_id}/approval");
        $superAdminView->assertOk();
        $superAdminView->assertSee('password123');
        $superAdminView->assertSee('nda-berkas-lengkap');
        $superAdminView->assertSee('Catatan perbaikan komprehensif dari pemeriksa');

        // Inspector show page should also display 'password123' and notes history
        $inspectorView = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}");
        $inspectorView->assertOk();
        $inspectorView->assertSee('password123');
        $inspectorView->assertSee('nda-berkas-lengkap');
        $inspectorView->assertSee('Catatan perbaikan komprehensif dari pemeriksa');
    }

    public function test_super_admin_return_displays_properly_on_admin_and_inspector_pages(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/RETURN/001',
            'document_date' => now(),
            'status' => InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL,
            'submitted_at' => now(),
            'admin_verified_at' => now(),
        ]);

        // 1. Super Admin returns inspection to Admin with reason 'perbaikilah'
        $response = $this->actingAs($this->superAdmin)->post("/super-admin/inspections/{$inspection->public_id}/approval", [
            'action' => 'return',
            'reason' => 'perbaikilah',
        ]);

        $response->assertRedirect(route('super-admin.inspections.pending'));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::ADMIN_REVISION_REQUIRED, $inspection->status);

        // 2. Admin verification index: returned inspection MUST appear in default list
        $adminIndex = $this->actingAs($this->admin)->get(route('admin.verifications.index'));
        $adminIndex->assertOk();
        $adminIndex->assertSee('TEST/RETURN/001');
        $adminIndex->assertSee('Perlu Perbaikan Admin');
        $adminIndex->assertSee('Dikembalikan oleh Super Admin');

        // 3. Admin verification detail: MUST display Super Admin note 'perbaikilah' and actionable buttons
        $adminDetail = $this->actingAs($this->admin)->get("/admin/verifications/{$inspection->public_id}");
        $adminDetail->assertOk();
        $adminDetail->assertSee('perbaikilah');
        $adminDetail->assertSee('Dikembalikan oleh Super Admin');
        $adminDetail->assertSee('Verifikasi Ulang &amp; Ajukan Kembali ke Super Admin', false);
        $adminDetail->assertSee('Teruskan Revisi ke Pemeriksa');

        // 4. Inspector task show page: MUST display Super Admin return info and explain that Admin is handling it
        $inspectorShow = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}");
        $inspectorShow->assertOk();
        $inspectorShow->assertSee('perbaikilah');
        $inspectorShow->assertSee('Perlu Perbaikan Admin');
        $inspectorShow->assertSee('Pemeriksaan Sedang Ditinjau / Diperbaiki oleh Admin Wilayah');
        $inspectorShow->assertDontSee('Perbaiki Pemeriksaan');

        // 5. Inspector cannot access revision form when status is ADMIN_REVISION_REQUIRED
        $inspectorRevision = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}/revision");
        $inspectorRevision->assertRedirect(route('inspector.tasks.show', $inspection));

        // 6. Inspector cannot access checklist form when status is ADMIN_REVISION_REQUIRED
        $inspectorChecklist = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}/checklist");
        $inspectorChecklist->assertRedirect(route('inspector.tasks.show', $inspection));

        // 7. Inspector cannot submit revision when status is ADMIN_REVISION_REQUIRED (Forbidden 403)
        $resubmitAttempt = $this->actingAs($this->inspector)->post("/inspector/tasks/{$inspection->public_id}/resubmit", [
            'notes' => 'Mencoba revisi tanpa izin admin',
        ]);
        $resubmitAttempt->assertForbidden();
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::ADMIN_REVISION_REQUIRED, $inspection->status);
    }

    public function test_admin_can_forward_returned_inspection_to_inspector(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/RETURN/002',
            'document_date' => now(),
            'status' => InspectionStatus::ADMIN_REVISION_REQUIRED,
            'submitted_at' => now(),
        ]);

        // Admin forwards revision to Inspector
        $response = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/request-revision", [
            'reason' => 'Mohon sesuaikan bukti stiker PC sesuai permintaan Super Admin',
        ]);

        $response->assertRedirect(route('admin.verifications.index'));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::REVISION_REQUIRED, $inspection->status);

        // Inspector sees the admin's revision note
        $inspectorShow = $this->actingAs($this->inspector)->get("/inspector/tasks/{$inspection->public_id}");
        $inspectorShow->assertOk();
        $inspectorShow->assertSee('Mohon sesuaikan bukti stiker PC sesuai permintaan Super Admin');
    }

    public function test_admin_can_reverify_and_submit_returned_inspection_to_super_admin(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/RETURN/003',
            'document_date' => now(),
            'status' => InspectionStatus::ADMIN_REVISION_REQUIRED,
            'submitted_at' => now(),
        ]);

        // Admin re-verifies and submits back to Super Admin in one step
        $response = $this->actingAs($this->admin)->post("/admin/verifications/{$inspection->public_id}/submit-approval", [
            'comment' => 'Data sudah dicek ulang dan telah valid',
        ]);

        $response->assertRedirect(route('admin.verifications.index'));
        $inspection->refresh();
        $this->assertEquals(InspectionStatus::PENDING_SUPER_ADMIN_APPROVAL, $inspection->status);
    }

    public function test_admin_dashboard_displays_alert_banner_when_inspection_needs_admin_revision(): void
    {
        $inspection = Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/ALERT/001',
            'document_date' => now(),
            'status' => InspectionStatus::ADMIN_REVISION_REQUIRED,
            'submitted_at' => now(),
        ]);

        \App\Models\InspectionNote::create([
            'inspection_id' => $inspection->id,
            'user_id' => $this->superAdmin->id,
            'note_type' => \App\Enums\NoteType::SUPER_ADMIN_REVISION,
            'note' => 'Periksa ulang bagian serial number dan checklist antivirus',
            'is_internal' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Perlu Perbaikan Admin');
        $response->assertSee('Pemeriksaan Memerlukan Tindakan / Revisi dari Admin');
        $response->assertSee('Periksa ulang bagian serial number dan checklist antivirus');
        $response->assertSee('TEST/ALERT/001');
        $response->assertSee($this->employee->name);
        $response->assertSee($this->employee->unit->name);
        $response->assertDontSee('(NIPP: ' . $this->employee->nipp . ')');
    }

    public function test_admin_verifications_index_displays_employee_and_unit_without_nipp(): void
    {
        Inspection::create([
            'inspection_period_id' => $this->period->id,
            'device_id' => $this->device->id,
            'employee_id' => $this->employee->id,
            'inspector_id' => $this->inspector->id,
            'assigned_by' => $this->admin->id,
            'reference_number' => 'TEST/NONIPP/001',
            'document_date' => now(),
            'status' => InspectionStatus::PENDING_ADMIN_VERIFICATION,
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.verifications.index'));
        $response->assertOk();
        $response->assertSee($this->employee->name);
        $response->assertSee($this->employee->unit->name);
        $response->assertDontSee('(NIPP: ' . $this->employee->nipp . ')');
    }
}

