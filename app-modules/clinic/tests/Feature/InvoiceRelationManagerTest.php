<?php

namespace Stella\Clinic\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Stella\Clinic\Models\Invoice;
use Stella\Clinic\Models\MedicalRecord;
use Tests\TestCase;

class InvoiceRelationManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_total_amount_is_calculated_automatically(): void
    {
        $invoice = Invoice::factory()->create([
            'consultation_fee' => 10000,
            'medicine_fee' => 5000,
        ]);

        $this->assertEquals(15000, $invoice->total_amount);
    }

    public function test_invoice_belongs_to_medical_record_and_patient(): void
    {
        $patient = User::factory()->create();
        $medicalRecord = MedicalRecord::factory()->create(['patient_id' => $patient->id]);
        
        $invoice = Invoice::factory()->create([
            'medical_record_id' => $medicalRecord->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertTrue($invoice->medicalRecord->is($medicalRecord));
        $this->assertTrue($invoice->patient->is($patient));
    }
}
