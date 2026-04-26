<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Enums\TriageStatus;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\Invoice;
use Stella\Clinic\Models\MedicalRecord;
use Stella\Pharmacy\Models\PharmacyRequest;
use Stella\Users\Models\Patient;
use Stella\Users\Models\PatientProfile;
use Tests\TestCase;

class PatientPharmacyFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Patient', 'guard_name' => 'web']);
        Role::create(['name' => 'Pharmacist', 'guard_name' => 'web']);
    }

    public function test_patient_creates_account_with_profile(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        PatientProfile::create([
            'user_id' => $patient->id,
            'date_of_birth' => '2000-08-20',
            'gender' => 'male',
            'blood_type' => 'O+',
            'emergency_contact' => '09777777777',
        ]);

        $this->assertTrue($patient->hasRole('Patient'));
        $this->assertDatabaseHas('users', ['phone' => '09333333333']);
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $patient->id,
            'blood_type' => 'O+',
        ]);
        $this->assertNotNull($patient->fresh()->patientProfile);
    }

    public function test_triage_log_is_created_and_routed_to_pharmacy(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'နှာရည်ယို, ချောင်းဆိုး, ကိုယ်အပူချိန်အနည်းငယ်တက်',
            'ai_analysis' => [
                'severity' => 'low',
                'category' => 'pharmacy',
                'reasoning' => 'Common cold symptoms manageable with OTC medication',
                'suggested_meds' => ['Paracetamol', 'Antihistamine', 'Vitamin C'],
            ],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $this->assertDatabaseHas('triage_logs', [
            'patient_id' => $patient->id,
            'severity' => 'low',
            'status' => 'to_pharmacy',
        ]);
        $this->assertEquals(Severity::LOW, $triageLog->severity);
        $this->assertEquals(TriageStatus::TO_PHARMACY, $triageLog->status);
        $this->assertTrue($triageLog->patient->is($patient));
        $this->assertEquals(
            ['Paracetamol', 'Antihistamine', 'Vitamin C'],
            $triageLog->ai_analysis['suggested_meds']
        );
    }

    public function test_pharmacy_request_is_created_with_suggested_medicines(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'နှာရည်ယို, ချောင်းဆိုး',
            'ai_analysis' => [
                'severity' => 'low',
                'category' => 'pharmacy',
                'reasoning' => 'Common cold symptoms manageable with OTC medication',
                'suggested_meds' => ['Paracetamol', 'Antihistamine', 'Vitamin C'],
            ],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $pharmacyRequest = PharmacyRequest::create([
            'triage_log_id' => $triageLog->id,
            'patient_id' => $patient->id,
            'suggested_medicines' => ['Paracetamol', 'Antihistamine', 'Vitamin C'],
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('pharmacy_requests', [
            'triage_log_id' => $triageLog->id,
            'patient_id' => $patient->id,
            'status' => 'pending',
        ]);
        $this->assertEquals(
            ['Paracetamol', 'Antihistamine', 'Vitamin C'],
            $pharmacyRequest->suggested_medicines
        );
        $this->assertNull($pharmacyRequest->pharmacist_id);
        $this->assertNull($pharmacyRequest->approved_medicines);
        $this->assertTrue($pharmacyRequest->patient->is($patient));
        $this->assertTrue($pharmacyRequest->triageLog->is($triageLog));
        $this->assertTrue($triageLog->fresh()->pharmacyRequest->is($pharmacyRequest));
    }

    public function test_pharmacist_approves_request_and_medical_record_is_auto_created(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'နှာရည်ယို, ချောင်းဆိုး',
            'ai_analysis' => [
                'severity' => 'low',
                'category' => 'pharmacy',
                'reasoning' => 'Common cold symptoms manageable with OTC medication',
                'suggested_meds' => ['Paracetamol', 'Antihistamine'],
            ],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $pharmacist = User::factory()->create(['name' => 'U Zaw Win']);
        $pharmacist->assignRole('Pharmacist');

        $pharmacyRequest = PharmacyRequest::create([
            'triage_log_id' => $triageLog->id,
            'patient_id' => $patient->id,
            'suggested_medicines' => ['Paracetamol', 'Antihistamine'],
            'status' => 'pending',
        ]);

        // Pharmacist reviews and approves the request
        $pharmacyRequest->update([
            'pharmacist_id' => $pharmacist->id,
            'approved_medicines' => ['Paracetamol', 'Antihistamine'],
            'status' => 'approved',
        ]);

        $this->assertEquals('approved', $pharmacyRequest->fresh()->status);
        $this->assertEquals($pharmacist->id, $pharmacyRequest->fresh()->pharmacist_id);

        // Medical record is auto-created by the PharmacyRequest booted hook
        $medicalRecord = MedicalRecord::where('patient_id', $patient->id)->first();

        $this->assertNotNull($medicalRecord);
        $this->assertEquals($patient->id, $medicalRecord->patient_id);
        $this->assertEquals($pharmacist->id, $medicalRecord->doctor_id);
        $this->assertEquals($triageLog->raw_symptoms, $medicalRecord->symptoms);
        $this->assertEquals(
            $triageLog->ai_analysis['reasoning'],
            $medicalRecord->final_diagnosis
        );
        $this->assertCount(2, $medicalRecord->prescriptions);
        $this->assertEquals('Paracetamol', $medicalRecord->prescriptions[0]['drug_name']);
        $this->assertEquals('Antihistamine', $medicalRecord->prescriptions[1]['drug_name']);
        $this->assertEquals(1, $medicalRecord->prescriptions[0]['quantity']);
        $this->assertTrue($medicalRecord->prescriptions[0]['is_available_in_clinic']);
    }

    public function test_rejected_pharmacy_request_does_not_create_medical_record(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'နှာရည်ယို',
            'ai_analysis' => [
                'severity' => 'low',
                'category' => 'pharmacy',
                'reasoning' => 'Minor cold',
                'suggested_meds' => ['Paracetamol'],
            ],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $pharmacyRequest = PharmacyRequest::create([
            'triage_log_id' => $triageLog->id,
            'patient_id' => $patient->id,
            'suggested_medicines' => ['Paracetamol'],
            'status' => 'pending',
        ]);

        $pharmacyRequest->update(['status' => 'rejected']);

        $this->assertEquals('rejected', $pharmacyRequest->fresh()->status);
        $this->assertDatabaseCount('medical_records', 0);
    }

    public function test_invoice_is_generated_for_pharmacy_flow(): void
    {
        $patient = Patient::create([
            'name' => 'Ko Aung',
            'phone' => '09333333333',
            'password' => 'password',
        ]);

        $pharmacist = User::factory()->create();
        $pharmacist->assignRole('Pharmacist');

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'နှာရည်ယို, ချောင်းဆိုး',
            'ai_analysis' => ['severity' => 'low', 'category' => 'pharmacy', 'reasoning' => 'Common cold', 'suggested_meds' => ['Paracetamol']],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $pharmacist->id,
            'triage_log_id' => $triageLog->id,
            'symptoms' => 'နှာရည်ယို, ချောင်းဆိုး',
            'final_diagnosis' => 'Common cold symptoms manageable with OTC medication',
            'prescriptions' => [
                [
                    'drug_name' => 'Paracetamol',
                    'dosage' => null,
                    'quantity' => 1,
                    'is_available_in_clinic' => true,
                    'unit_price' => null,
                ],
            ],
        ]);

        $invoice = Invoice::create([
            'medical_record_id' => $medicalRecord->id,
            'patient_id' => $patient->id,
            'consultation_fee' => 0,
            'medicine_fee' => 2500,
            'status' => 'unpaid',
        ]);

        $this->assertEquals(2500, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertTrue($invoice->medicalRecord->is($medicalRecord));
        $this->assertTrue($invoice->patient->is($patient));
    }

    public function test_complete_pharmacy_flow(): void
    {
        // Step 1: Patient creates account with profile
        $patient = Patient::create([
            'name' => 'Ma Thida',
            'phone' => '09444444444',
            'password' => 'password',
        ]);

        PatientProfile::create([
            'user_id' => $patient->id,
            'date_of_birth' => '1993-11-05',
            'gender' => 'female',
            'blood_type' => 'AB+',
            'chronic_illnesses' => 'None',
            'emergency_contact' => '09666666666',
        ]);

        $this->assertTrue($patient->hasRole('Patient'));
        $this->assertNotNull($patient->fresh()->patientProfile);

        // Step 2: Triage log created (AI routes to pharmacy — low severity)
        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'ဝမ်းလျောမှု, ဗိုက်ကိုက်မှု',
            'ai_analysis' => [
                'severity' => 'low',
                'category' => 'pharmacy',
                'reasoning' => 'Mild gastrointestinal symptoms manageable with OTC medication',
                'suggested_meds' => ['ORS', 'Loperamide', 'Antacid'],
            ],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_PHARMACY,
        ]);

        $this->assertEquals(TriageStatus::TO_PHARMACY, $triageLog->status);
        $this->assertEquals(Severity::LOW, $triageLog->severity);

        // Step 3: Pharmacy request created with AI-suggested medicines
        $pharmacyRequest = PharmacyRequest::create([
            'triage_log_id' => $triageLog->id,
            'patient_id' => $patient->id,
            'suggested_medicines' => $triageLog->ai_analysis['suggested_meds'],
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $pharmacyRequest->status);
        $this->assertEquals(['ORS', 'Loperamide', 'Antacid'], $pharmacyRequest->suggested_medicines);

        // Step 4: Pharmacist reviews and approves (removes Loperamide as not in stock)
        $pharmacist = User::factory()->create(['name' => 'Daw Su Su']);
        $pharmacist->assignRole('Pharmacist');

        $pharmacyRequest->update([
            'pharmacist_id' => $pharmacist->id,
            'approved_medicines' => ['ORS', 'Antacid'],
            'status' => 'approved',
        ]);

        $this->assertEquals('approved', $pharmacyRequest->fresh()->status);

        // Medical record is auto-created by the booted hook
        $medicalRecord = MedicalRecord::where('patient_id', $patient->id)->first();

        $this->assertNotNull($medicalRecord);
        $this->assertEquals($pharmacist->id, $medicalRecord->doctor_id);
        $this->assertEquals($triageLog->raw_symptoms, $medicalRecord->symptoms);
        $this->assertEquals(
            'Mild gastrointestinal symptoms manageable with OTC medication',
            $medicalRecord->final_diagnosis
        );
        $this->assertCount(2, $medicalRecord->prescriptions);

        // Step 5: Invoice generated for medicines dispensed
        $invoice = Invoice::create([
            'medical_record_id' => $medicalRecord->id,
            'patient_id' => $patient->id,
            'consultation_fee' => 0,
            'medicine_fee' => 3500,
            'status' => 'unpaid',
        ]);

        $this->assertEquals(3500, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);

        // Verify the full relationship chain
        $this->assertTrue($invoice->medicalRecord->is($medicalRecord));
        $this->assertTrue($invoice->patient->is($patient));
        $this->assertTrue($triageLog->fresh()->pharmacyRequest->is($pharmacyRequest));
        $this->assertEquals($pharmacist->id, $pharmacyRequest->fresh()->pharmacist_id);
    }
}
