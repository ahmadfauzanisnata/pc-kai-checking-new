<?php

namespace Database\Factories;

use App\Enums\DeviceStatus;
use App\Enums\DeviceType;
use App\Models\Device;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    public function definition(): array
    {
        return [
            'employee_id' => Employee::first()?->id ?? Employee::factory(),
            'asset_number' => 'AST-' . fake()->unique()->numerify('#####'),
            'hostname' => 'HOST-' . fake()->lexify('????'),
            'device_type' => fake()->randomElement([DeviceType::PC, DeviceType::LAPTOP]),
            'brand' => fake()->randomElement(['Lenovo', 'Dell', 'HP', 'Asus']),
            'model' => 'Model ' . fake()->numerify('###'),
            'serial_number' => 'SN-' . fake()->unique()->numerify('########'),
            'ip_address' => fake()->ipv4(),
            'operating_system' => 'Windows 11 Pro',
            'os_version' => '23H2',
            'device_status' => DeviceStatus::ACTIVE,
            'description' => 'Perangkat uji coba',
        ];
    }
}
