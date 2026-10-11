<?php

namespace Tests\Feature;

use App\Imports\EmployeeImport;
use App\Models\BusinessArea;
use App\Models\Employee;
use App\Models\Role;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class EmployeeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BusinessArea $businessArea;
    protected Unit $unitKeu;
    protected Unit $unitSdm;

    protected function setUp(): void
    {
        parent::setUp();

        $roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin']);

        $this->businessArea = BusinessArea::create([
            'code' => 'DAOP-2',
            'name' => 'Daop 2 Bandung',
            'is_active' => true,
        ]);

        $this->unitKeu = Unit::create([
            'business_area_id' => $this->businessArea->id,
            'code' => 'KEU',
            'name' => 'Unit Keuangan',
            'is_active' => true,
        ]);

        $this->unitSdm = Unit::create([
            'business_area_id' => $this->businessArea->id,
            'code' => 'SDM',
            'name' => 'Unit SDM',
            'is_active' => true,
        ]);

        $this->admin = User::create([
            'role_id' => $roleAdmin->id,
            'business_area_id' => $this->businessArea->id,
            'unit_id' => $this->unitKeu->id,
            'name' => 'Admin Daop 2',
            'nipp' => 'ADM001',
            'email' => 'admin@daop2.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
    }

    public function test_employee_index_displays_kode_area_and_no_default_unit_in_import_modal(): void
    {
        $employee = Employee::create([
            'unit_id' => $this->unitKeu->id,
            'nipp' => 'EMP001',
            'name' => 'Budi Santoso',
            'position' => 'Staff Keuangan',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.employees.index'));

        $response->assertStatus(200);
        $response->assertSee('Kode Area');
        $response->assertSee('DAOP-2');
        $response->assertDontSee('Unit Kerja Default (Jika kolom kode_unit kosong)');
    }

    public function test_import_with_invalid_kode_unit_fails_only_that_row(): void
    {
        $import = new EmployeeImport($this->businessArea->id);

        $rows = new Collection([
            [
                'nipp' => 'NIPP001',
                'nama_pegawai' => 'Pegawai Satu',
                'kode_unit' => 'KEU',
                'email' => 'satu@kai.id',
                'jabatan' => 'Staff Keuangan',
            ],
            [
                'nipp' => 'NIPP002',
                'nama_pegawai' => 'Pegawai Dua',
                'kode_unit' => 'KUE', // INVALID CODE
                'email' => 'dua@kai.id',
                'jabatan' => 'Staff Operasional',
            ],
            [
                'nipp' => 'NIPP003',
                'nama_pegawai' => 'Pegawai Tiga',
                'kode_unit' => 'SDM',
                'email' => 'tiga@kai.id',
                'jabatan' => 'Staff SDM',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(2, $import->getSuccessCount());
        $this->assertEquals(1, $import->getFailedCount());
        $this->assertCount(1, $import->getErrors());
        $this->assertStringContainsString("Kode unit 'KUE' tidak ditemukan", $import->getErrors()[0]);

        // Pegawai Satu and Tiga must exist in database
        $this->assertDatabaseHas('employees', [
            'nipp' => 'NIPP001',
            'name' => 'Pegawai Satu',
            'unit_id' => $this->unitKeu->id,
        ]);
        $this->assertDatabaseHas('employees', [
            'nipp' => 'NIPP003',
            'name' => 'Pegawai Tiga',
            'unit_id' => $this->unitSdm->id,
        ]);

        // Pegawai Dua must NOT exist in database
        $this->assertDatabaseMissing('employees', [
            'nipp' => 'NIPP002',
        ]);
    }

    public function test_import_with_empty_kode_unit_fails(): void
    {
        $import = new EmployeeImport($this->businessArea->id);

        $rows = new Collection([
            [
                'nipp' => 'NIPP004',
                'nama_pegawai' => 'Pegawai Empat',
                'kode_unit' => '', // EMPTY
                'email' => 'empat@kai.id',
                'jabatan' => 'Staff',
            ],
        ]);

        $import->collection($rows);

        $this->assertEquals(0, $import->getSuccessCount());
        $this->assertEquals(1, $import->getFailedCount());
        $this->assertStringContainsString('Kode unit kerja wajib diisi', $import->getErrors()[0]);

        $this->assertDatabaseMissing('employees', [
            'nipp' => 'NIPP004',
        ]);
    }

    public function test_employee_export_contains_kode_area_header(): void
    {
        $export = new \App\Exports\EmployeeExport();
        $headings = $export->headings();

        $this->assertContains('Kode Area', $headings);
        $this->assertNotContains('Business Area', $headings);
    }
}
