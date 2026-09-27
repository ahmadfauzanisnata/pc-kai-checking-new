<?php

namespace Database\Factories;

use App\Models\BusinessArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class BusinessAreaFactory extends Factory
{
    protected $model = BusinessArea::class;

    public function definition(): array
    {
        return [
            'code' => 'BA_' . strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->city() . ' Area',
            'address' => fake()->address(),
            'is_active' => true,
        ];
    }
}
