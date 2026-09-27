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
}
