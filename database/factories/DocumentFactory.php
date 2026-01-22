<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'document_number' => fake()->unique()->numerify('DOC-####-####'),
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'effective_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'expiry_date' => fake()->dateTimeBetween('+1 month', '+3 years'),
            'status' => Document::STATUS_DRAFT,
            'confidentiality' => 'internal',
            'current_version' => 1,
            'download_count' => 0,
            'is_locked' => false,
        ];
    }

    /**
     * Configure the factory to set the creator.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Document $document) {
            // Ensure document type exists
            if (!$document->document_type_id) {
                $document->document_type_id = DocumentType::factory()->create()->id;
            }
        });
    }

    /**
     * Indicate that the document is in draft status.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_DRAFT,
        ]);
    }

    /**
     * Indicate that the document is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_EXPIRED,
            'expiry_date' => fake()->dateTimeBetween('-1 year', '-1 day'),
        ]);
    }

    /**
     * Indicate that the document is pending approval.
     */
    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_PENDING_APPROVAL,
        ]);
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the document is archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Document::STATUS_ARCHIVED,
        ]);
    }

    /**
     * Set the document creator.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }

    /**
     * Set the document type.
     */
    public function ofType(DocumentType $type): static
    {
        return $this->state(fn (array $attributes) => [
            'document_type_id' => $type->id,
        ]);
    }

    /**
     * Indicate that the document is confidential.
     */
    public function confidential(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidentiality' => 'confidential',
        ]);
    }

    /**
     * Indicate that the document is public.
     */
    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'confidentiality' => 'public',
        ]);
    }

    /**
     * Indicate that the document is expiring soon (within 30 days).
     */
    public function expiringSoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'expiry_date' => fake()->dateTimeBetween('+1 day', '+30 days'),
        ]);
    }
}
