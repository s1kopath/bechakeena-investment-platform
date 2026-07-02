<?php

namespace Database\Factories;

use App\Enums\KycDocumentStatus;
use App\Enums\KycDocumentType;
use App\Models\KycDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<KycDocument>
 */
class KycDocumentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => KycDocumentType::Nid,
            'document_number' => fake()->numerify('##########'),
            'front_image_path' => 'kyc/'.fake()->uuid().'-front.jpg',
            'back_image_path' => 'kyc/'.fake()->uuid().'-back.jpg',
            'selfie_path' => null,
            'status' => KycDocumentStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => KycDocumentStatus::Approved,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => KycDocumentStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }

    public function passport(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => KycDocumentType::Passport,
        ]);
    }
}
