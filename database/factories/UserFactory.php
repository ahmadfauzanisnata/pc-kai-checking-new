<?php

namespace Database\Factories;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'role_id' => Role::firstOrCreate(['name' => 'inspector'], ['display_name' => 'Pemeriksa'])->id,
            'name' => fake()->name(),
            'nipp' => 'NIPP' . fake()->unique()->numerify('#####'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'super_admin'], ['display_name' => 'Super Admin'])->id,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Admin'])->id,
        ]);
    }

    public function inspector(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::firstOrCreate(['name' => 'inspector'], ['display_name' => 'Pemeriksa'])->id,
        ]);
    }
}
