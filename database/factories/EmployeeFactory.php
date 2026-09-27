<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'unit_id' => Unit::first()?->id ?? Unit::factory(),
            'nipp' => 'EMP' . fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'position' => fake()->jobTitle(),
            'is_active' => true,
        ];
    }
}
