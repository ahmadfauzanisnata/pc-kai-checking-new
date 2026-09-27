<?php

namespace Tests\Feature;

use App\Models\BusinessArea;
use App\Models\Device;
use App\Models\Employee;
use App\Models\Inspection;
use App\Models\InspectionPeriod;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Tests\TestCase;

class ApiTest extends TestCase
{
    protected Role $superAdminRole;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->superAdminRole = Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin', 'level' => 1]);

        $this->user = User::factory()->create([
            'email' => 'api_user@example.com',
            'password' => bcrypt('password123'),
            'role_id' => $this->superAdminRole->id,
            'is_active' => true,
        ]);
    }

    public function test_api_login_success(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api_user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => ['token', 'user'],
            ]);
    }

    public function test_api_login_failure(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api_user@example.com',
            'password' => 'wrong-pass',
        ]);

        $response->assertStatus(422);
    }

    public function test_api_me_authenticated(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('data.email', 'api_user@example.com');
    }

    public function test_api_dashboard_stats(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['total_business_areas', 'total_devices']]);
    }

    public function test_api_master_data(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $ba = BusinessArea::factory()->create(['code' => 'API_BA']);
        $unit = Unit::factory()->create(['business_area_id' => $ba->id]);

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/master/business-areas');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $responseUnits = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/master/units');

        $responseUnits->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_api_reports_summary(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/reports/summary');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['total_inspections', 'compliance_rate_percent']]);
    }

    public function test_api_logout(): void
    {
        $token = $this->user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }
}
