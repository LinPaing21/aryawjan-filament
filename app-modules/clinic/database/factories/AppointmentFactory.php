<?php

namespace Stella\Clinic\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Stella\Clinic\Models\Appointment;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
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
            'doctor_id' => User::factory(),
            'token_number' => now()->format('ymd') . '-' . str_pad($this->faker->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'scheduled_at' => now()->addDay()->setHour(9)->setMinute(0)->setSecond(0),
            'status' => 'booked',
        ];
    }
}
