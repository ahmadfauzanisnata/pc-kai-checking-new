<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'super_admin',
                'display_name' => 'Super Admin',
                'description' => 'Memiliki hak akses penuh ke seluruh sistem dan persetujuan akhir.',
            ],
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Mengelola data master, periode, penugasan, dan verifikasi pemeriksaan di business area miliknya.',
            ],
            [
                'name' => 'inspector',
                'display_name' => 'Pemeriksa',
                'description' => 'Melakukan pemeriksaan fisik dan teknis perangkat PC/Laptop sesuai tugas.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }
    }
}
