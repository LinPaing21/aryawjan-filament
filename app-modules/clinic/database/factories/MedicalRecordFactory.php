<?php

namespace Stella\Clinic\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\MedicalRecord;

/**
 * @extends Factory<MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    protected $model = MedicalRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $patient = User::factory()->create();

        return [
            'patient_id' => $patient->id,
            'doctor_id' => User::factory(),
            'triage_log_id' => TriageLog::create([
                'patient_id' => $patient->id,
                'raw_symptoms' => $this->faker->sentence(),
                'ai_analysis' => [
                    'severity' => 'low',
                    'category' => 'consultation',
                    'reasoning' => $this->faker->sentence(),
                    'suggested_meds' => [],
                ],
                'severity' => 'low',
                'status' => 'to_doctor',
            ])->id,
            'symptoms' => $this->faker->paragraph(),
            'final_diagnosis' => $this->faker->word(),
        ];
    }
}
