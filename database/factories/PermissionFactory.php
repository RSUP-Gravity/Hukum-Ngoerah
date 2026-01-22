<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word() . '.' . fake()->word(),
            'display_name' => fake()->words(3, true),
            'module' => fake()->word(),
        ];
    }

    /**
     * Document view permission.
     */
    public function documentsView(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'documents.view',
            'display_name' => 'Lihat Dokumen',
            'module' => 'documents',
        ]);
    }

    /**
     * Document create permission.
     */
    public function documentsCreate(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'documents.create',
            'display_name' => 'Buat Dokumen',
            'module' => 'documents',
        ]);
    }

    /**
     * Document edit permission.
     */
    public function documentsEdit(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'documents.edit',
            'display_name' => 'Edit Dokumen',
            'module' => 'documents',
        ]);
    }

    /**
     * Document delete permission.
     */
    public function documentsDelete(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'documents.delete',
            'display_name' => 'Hapus Dokumen',
            'module' => 'documents',
        ]);
    }

    /**
     * Document download permission.
     */
    public function documentsDownload(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'documents.download',
            'display_name' => 'Download Dokumen',
            'module' => 'documents',
        ]);
    }
}
