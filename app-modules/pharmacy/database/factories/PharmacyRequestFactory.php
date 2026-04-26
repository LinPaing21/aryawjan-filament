<?php

namespace Stella\Pharmacy\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Stella\Pharmacy\Models\PharmacyRequest;

/**
 * @extends Factory<PharmacyRequest>
 */
class PharmacyRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'patient_id' => User::factory(),
            'suggested_medicines' => $this->faker->words(3),
            'status' => 'pending',
        ];
    }
}
