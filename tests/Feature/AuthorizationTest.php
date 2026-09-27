<?php

namespace Tests\Feature;

use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    protected Role $superAdminRole;
    protected Role $adminRole;
    protected Role $inspectorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin', 'level' => 1]);
        $this->adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin Unit', 'level' => 2]);
        $this->inspectorRole = Role::firstOrCreate(['name' => 'inspector'], ['display_name' => 'Pemeriksa', 'level' => 3]);
    }

    public function test_inspector_cannot_access_super_admin_dashboard(): void
    {
        $inspector = User::factory()->create(['role_id' => $this->inspectorRole->id]);

        $response = $this->actingAs($inspector)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_inspector_cannot_access_admin_dashboard(): void
    {
        $inspector = User::factory()->create(['role_id' => $this->inspectorRole->id]);

        $response = $this->actingAs($inspector)->get('/admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_admin_cannot_access_super_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role_id' => $this->adminRole->id]);

        $response = $this->actingAs($admin)->get('/super-admin/dashboard');

        $response->assertStatus(403);
    }

    public function test_super_admin_can_access_all_dashboards(): void
    {
        $superAdmin = User::factory()->create(['role_id' => $this->superAdminRole->id]);

        $responseSA = $this->actingAs($superAdmin)->get('/super-admin/dashboard');
        $responseSA->assertStatus(200);

        $responseAdmin = $this->actingAs($superAdmin)->get('/admin/dashboard');
        $responseAdmin->assertStatus(200);

        $responseInsp = $this->actingAs($superAdmin)->get('/inspector/dashboard');
        $responseInsp->assertStatus(200);
    }

    public function test_admin_cannot_edit_devices_outside_their_business_area(): void
    {
        $ba1 = BusinessArea::factory()->create(['code' => 'TEST_BA1']);
        $ba2 = BusinessArea::factory()->create(['code' => 'TEST_BA2']);

        $admin = User::factory()->create([
            'role_id' => $this->adminRole->id,
            'business_area_id' => $ba1->id,
        ]);

        $unit2 = Unit::factory()->create(['business_area_id' => $ba2->id]);
        $emp2 = Employee::factory()->create(['unit_id' => $unit2->id]);
        $deviceOutside = Device::factory()->create(['employee_id' => $emp2->id]);

        // Device edit route checks if admin's BA matches
        $response = $this->actingAs($admin)->get("/admin/devices/{$deviceOutside->id}/edit");
        $response->assertStatus(403);
    }
}
