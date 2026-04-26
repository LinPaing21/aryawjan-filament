<?php

namespace Stella\Clinic\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Stella\Clinic\Models\Invoice;
use Stella\Clinic\Models\MedicalRecord;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'patient_id' => User::factory(),
            'consultation_fee' => 5000,
            'medicine_fee' => 3000,
            'status' => 'unpaid',
        ];
    }
}
