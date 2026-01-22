<?php

namespace Database\Factories;

use App\Models\Unit;
use App\Models\Directorate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Unit>
 */
class UnitFactory extends Factory
{
    protected $model = Unit::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        
        return [
            'directorate_id' => Directorate::factory(),
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'name' => ucwords($name),
            'description' => $this->faker->optional()->sentence(),
            'is_active' => true,
            'sort_order' => $this->faker->numberBetween(0, 100),
        ];
    }

    /**
     * Indicate that the unit is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create unit with specific directorate.
     */
    public function forDirectorate(Directorate $directorate): static
    {
        return $this->state(fn (array $attributes) => [
            'directorate_id' => $directorate->id,
        ]);
    }
}
