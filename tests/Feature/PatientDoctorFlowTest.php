<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Enums\TriageStatus;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Models\Invoice;
use Stella\Clinic\Models\MedicalRecord;
use Stella\Users\Models\Patient;
use Stella\Users\Models\PatientProfile;
use Tests\TestCase;

class PatientDoctorFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'Patient', 'guard_name' => 'web']);
        Role::create(['name' => 'Doctor', 'guard_name' => 'web']);
    }

    public function test_patient_creates_account_with_profile(): void
    {
        $patient = Patient::create([
            'name' => 'Mg Mg',
            'phone' => '09111111111',
            'password' => 'password',
        ]);

        PatientProfile::create([
            'user_id' => $patient->id,
            'date_of_birth' => '1995-05-15',
            'gender' => 'male',
            'blood_type' => 'A+',
            'allergies' => 'Penicillin',
            'emergency_contact' => '09999999999',
        ]);

        $this->assertTrue($patient->hasRole('Patient'));
        $this->assertDatabaseHas('users', ['phone' => '09111111111']);
        $this->assertDatabaseHas('patient_profiles', [
            'user_id' => $patient->id,
            'blood_type' => 'A+',
            'gender' => 'male',
        ]);
        $this->assertNotNull($patient->fresh()->patientProfile);
        $this->assertEquals('Penicillin', $patient->fresh()->patientProfile->allergies);
    }

    public function test_triage_log_is_created_and_routed_to_doctor(): void
    {
        $patient = Patient::create([
            'name' => 'Mg Mg',
            'phone' => '09111111111',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'ဦးခေါင်းကိုက်, ဖျားနာ, အဆစ်နာကျင်မှု',
            'ai_analysis' => [
                'severity' => 'high',
                'category' => 'consultation',
                'reasoning' => 'Symptoms indicate possible dengue fever requiring doctor consultation',
                'suggested_meds' => [],
            ],
            'severity' => Severity::HIGH,
            'status' => TriageStatus::TO_DOCTOR,
        ]);

        $this->assertDatabaseHas('triage_logs', [
            'patient_id' => $patient->id,
            'severity' => 'high',
            'status' => 'to_doctor',
        ]);
        $this->assertEquals(Severity::HIGH, $triageLog->severity);
        $this->assertEquals(TriageStatus::TO_DOCTOR, $triageLog->status);
        $this->assertTrue($triageLog->patient->is($patient));
        $this->assertEquals('ဦးခေါင်းကိုက်, ဖျားနာ, အဆစ်နာကျင်မှု', $triageLog->raw_symptoms);
    }

    public function test_appointment_is_booked_linked_to_triage_log(): void
    {
        $patient = Patient::create([
            'name' => 'Mg Mg',
            'phone' => '09111111111',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'ဦးခေါင်းကိုက်',
            'ai_analysis' => [
                'severity' => 'high',
                'category' => 'consultation',
                'reasoning' => 'Needs doctor consultation',
                'suggested_meds' => [],
            ],
            'severity' => Severity::HIGH,
            'status' => TriageStatus::TO_DOCTOR,
        ]);

        $doctor = User::factory()->create(['name' => 'Dr. Ko Ko']);
        $doctor->assignRole('Doctor');

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'token_number' => '260423-01',
            'scheduled_at' => now()->addDay()->setHour(10)->setMinute(0)->setSecond(0),
            'status' => 'booked',
        ]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'status' => 'booked',
            'token_number' => '260423-01',
        ]);
        $this->assertTrue($appointment->patient->is($patient));
        $this->assertTrue($appointment->doctor->is($doctor));
        $this->assertTrue($appointment->triageLog->is($triageLog));
        $this->assertTrue($triageLog->fresh()->appointment->is($appointment));
    }

    public function test_doctor_creates_medical_record_with_prescriptions(): void
    {
        $patient = Patient::create([
            'name' => 'Mg Mg',
            'phone' => '09111111111',
            'password' => 'password',
        ]);

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'ဦးခေါင်းကိုက်, ဖျားနာ',
            'ai_analysis' => [
                'severity' => 'high',
                'category' => 'consultation',
                'reasoning' => 'Needs doctor consultation',
                'suggested_meds' => [],
            ],
            'severity' => Severity::HIGH,
            'status' => TriageStatus::TO_DOCTOR,
        ]);

        $doctor = User::factory()->create(['name' => 'Dr. Ko Ko']);
        $doctor->assignRole('Doctor');

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'symptoms' => 'ဦးခေါင်းကိုက်, ဖျားနာ, အဆစ်နာကျင်မှု',
            'physical_examination' => 'Temperature: 38.5°C, BP: 120/80',
            'ai_suggestions' => ['Possible viral infection or dengue fever'],
            'final_diagnosis' => 'Dengue Fever (Suspected)',
            'doctor_notes' => 'Patient needs complete blood count test',
            'prescriptions' => [
                [
                    'drug_name' => 'Paracetamol',
                    'dosage' => '500mg',
                    'quantity' => 10,
                    'is_available_in_clinic' => true,
                    'unit_price' => 500,
                ],
                [
                    'drug_name' => 'ORS Sachets',
                    'dosage' => '1 sachet in 1L water',
                    'quantity' => 5,
                    'is_available_in_clinic' => true,
                    'unit_price' => 200,
                ],
            ],
        ]);

        $this->assertDatabaseHas('medical_records', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'final_diagnosis' => 'Dengue Fever (Suspected)',
        ]);
        $this->assertTrue($medicalRecord->patient->is($patient));
        $this->assertTrue($medicalRecord->doctor->is($doctor));
        $this->assertTrue($medicalRecord->triageLog->is($triageLog));
        $this->assertCount(2, $medicalRecord->prescriptions);
        $this->assertEquals('Paracetamol', $medicalRecord->prescriptions[0]['drug_name']);
        $this->assertEquals(500, $medicalRecord->prescriptions[0]['unit_price']);
        $this->assertEquals('ORS Sachets', $medicalRecord->prescriptions[1]['drug_name']);
    }

    public function test_invoice_is_generated_with_correct_total_for_doctor_flow(): void
    {
        $patient = Patient::create([
            'name' => 'Mg Mg',
            'phone' => '09111111111',
            'password' => 'password',
        ]);

        $doctor = User::factory()->create();
        $doctor->assignRole('Doctor');

        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'ဦးခေါင်းကိုက်',
            'ai_analysis' => ['severity' => 'low', 'category' => 'consultation', 'reasoning' => 'Minor issue', 'suggested_meds' => []],
            'severity' => Severity::LOW,
            'status' => TriageStatus::TO_DOCTOR,
        ]);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'symptoms' => 'ဦးခေါင်းကိုက်',
            'final_diagnosis' => 'Common Cold',
        ]);

        $invoice = Invoice::create([
            'medical_record_id' => $medicalRecord->id,
            'patient_id' => $patient->id,
            'consultation_fee' => 5000,
            'medicine_fee' => 3000,
            'status' => 'unpaid',
        ]);

        $this->assertEquals(8000, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);
        $this->assertNull($invoice->payment_method);
        $this->assertTrue($invoice->medicalRecord->is($medicalRecord));
        $this->assertTrue($invoice->patient->is($patient));

        $invoice->update(['status' => 'paid', 'payment_method' => 'cash']);

        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertEquals('cash', $invoice->fresh()->payment_method);
        $this->assertEquals(8000, $invoice->fresh()->total_amount);
    }

    public function test_complete_doctor_consultation_flow(): void
    {
        // Step 1: Patient creates account with profile
        $patient = Patient::create([
            'name' => 'Ma Aye',
            'phone' => '09222222222',
            'password' => 'password',
        ]);

        PatientProfile::create([
            'user_id' => $patient->id,
            'date_of_birth' => '1988-03-12',
            'gender' => 'female',
            'blood_type' => 'B+',
            'allergies' => 'Penicillin',
            'emergency_contact' => '09888888888',
        ]);

        $this->assertTrue($patient->hasRole('Patient'));
        $this->assertNotNull($patient->fresh()->patientProfile);

        // Step 2: Triage log created (AI routes to doctor — high severity)
        $triageLog = TriageLog::create([
            'patient_id' => $patient->id,
            'raw_symptoms' => 'မျက်လည်, ရင်ကျပ်, နှလုံးခုန်မြန်မှု',
            'ai_analysis' => [
                'severity' => 'high',
                'category' => 'consultation',
                'reasoning' => 'Cardiac symptoms require immediate doctor evaluation',
                'suggested_meds' => [],
            ],
            'severity' => Severity::HIGH,
            'status' => TriageStatus::TO_DOCTOR,
        ]);

        $this->assertEquals(TriageStatus::TO_DOCTOR, $triageLog->status);
        $this->assertEquals(Severity::HIGH, $triageLog->severity);

        // Step 3: Appointment booked with a doctor
        $doctor = User::factory()->create(['name' => 'Dr. Kyaw Zin']);
        $doctor->assignRole('Doctor');

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'token_number' => '260424-01',
            'scheduled_at' => now()->addDay()->setHour(9)->setMinute(0)->setSecond(0),
            'status' => 'booked',
        ]);

        $this->assertEquals('booked', $appointment->status);
        $this->assertTrue($appointment->triageLog->is($triageLog));

        // Step 4: Appointment completed; doctor creates medical record
        $appointment->update(['status' => 'completed']);

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'triage_log_id' => $triageLog->id,
            'symptoms' => $triageLog->raw_symptoms,
            'physical_examination' => 'BP: 150/95, Pulse: 110 bpm, SpO2: 96%',
            'ai_suggestions' => ['Possible arrhythmia or anxiety-induced palpitations'],
            'final_diagnosis' => 'Hypertension with Palpitations',
            'doctor_notes' => 'Refer for ECG and Holter monitoring',
            'prescriptions' => [
                [
                    'drug_name' => 'Amlodipine',
                    'dosage' => '5mg',
                    'quantity' => 30,
                    'is_available_in_clinic' => true,
                    'unit_price' => 200,
                ],
                [
                    'drug_name' => 'Metoprolol',
                    'dosage' => '25mg',
                    'quantity' => 30,
                    'is_available_in_clinic' => false,
                    'unit_price' => 350,
                ],
            ],
        ]);

        $this->assertEquals('Hypertension with Palpitations', $medicalRecord->final_diagnosis);
        $this->assertCount(2, $medicalRecord->prescriptions);
        $this->assertTrue($medicalRecord->triageLog->is($triageLog));

        // Step 5: Invoice generated with total costs (consultation + medicines)
        $invoice = Invoice::create([
            'medical_record_id' => $medicalRecord->id,
            'patient_id' => $patient->id,
            'consultation_fee' => 10000,
            'medicine_fee' => 16500,
            'status' => 'unpaid',
        ]);

        $this->assertEquals(26500, $invoice->total_amount);
        $this->assertEquals('unpaid', $invoice->status);

        // Verify the full relationship chain
        $this->assertTrue($invoice->medicalRecord->is($medicalRecord));
        $this->assertTrue($invoice->patient->is($patient));
        $this->assertTrue($medicalRecord->triageLog->is($triageLog));
        $this->assertTrue($triageLog->fresh()->appointment->is($appointment));
        $this->assertTrue($appointment->patient->is($patient));
        $this->assertEquals('completed', $appointment->fresh()->status);
    }
}
