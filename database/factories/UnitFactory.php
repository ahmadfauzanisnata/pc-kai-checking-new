<?php

namespace Database\Factories;

use App\Models\BusinessArea;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'business_area_id' => BusinessArea::first()?->id ?? BusinessArea::factory(),
            'code' => 'UNIT_' . strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->company() . ' Unit',
            'is_active' => true,
        ];
    }
}
