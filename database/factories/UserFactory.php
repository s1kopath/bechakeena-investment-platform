<?php

namespace Database\Factories;

use App\Enums\KycStatus;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->e164PhoneNumber(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'kyc_status' => KycStatus::Pending,
            'status' => UserStatus::Active,
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the investor has cleared KYC.
     */
    public function kycApproved(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => KycStatus::Approved,
        ]);
    }

    /**
     * Indicate that the investor has submitted KYC awaiting review.
     */
    public function kycSubmitted(): static
    {
        return $this->state(fn (array $attributes) => [
            'kyc_status' => KycStatus::Submitted,
        ]);
    }

    /**
     * An OAuth-only account (no password, verified email).
     */
    public function google(): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => null,
            'phone' => null,
            'google_id' => (string) fake()->unique()->randomNumber(9, true),
            'email_verified_at' => now(),
        ]);
    }

    /**
     * A deactivated account.
     */
    public function deactivated(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => UserStatus::Deactivated,
        ]);
    }
}
