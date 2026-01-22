<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'display_name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }

    /**
     * Super Admin role.
     */
    public function superAdmin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'super_admin',
            'display_name' => 'Super Admin',
        ]);
    }

    /**
     * Admin role.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'admin',
            'display_name' => 'Admin',
        ]);
    }

    /**
     * Executive role.
     */
    public function executive(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'executive',
            'display_name' => 'Executive',
        ]);
    }

    /**
     * General user role.
     */
    public function general(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'general',
            'display_name' => 'General User',
        ]);
    }
}
