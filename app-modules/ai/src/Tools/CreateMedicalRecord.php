<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Models\MedicalRecord;
use Stringable;

class CreateMedicalRecord implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Creates a new medical record for a patient after diagnosis. Use this tool when the doctor has completed their diagnosis and wants to save the record including symptoms, physical examination findings, final diagnosis, doctor notes, and prescriptions.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        Appointment::where('triage_log_id', $request['triageLogId'])->update(['status' => 'completed']);

        $record = MedicalRecord::create([
            'patient_id' => $request['patientId'],
            'doctor_id' => $request['doctorId'],
            'triage_log_id' => $request['triageLogId'],
            'symptoms' => $request['symptoms'],
            'physical_examination' => $request['physicalExamination'] ?? null,
            'final_diagnosis' => $request['finalDiagnosis'],
            'doctor_notes' => $request['doctorNotes'] ?? null,
            'prescriptions' => $request['prescriptions'] ?? null,
        ]);

        return "Medical record created successfully with ID: {$record->id} for patient ID: {$record->patient_id}.";
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'patientId' => $schema->integer()->required(),
            'doctorId' => $schema->integer()->required(),
            'triageLogId' => $schema->integer()->required(),
            'symptoms' => $schema->string()->required(),
            'physicalExamination' => $schema->string(),
            'finalDiagnosis' => $schema->string()->required(),
            'doctorNotes' => $schema->string(),
            'prescriptions' => $schema->array()->items(
                $schema->object([
                    'drug_name' => $schema->string()->required(),
                    'dosage' => $schema->string()->required(),
                    'quantity' => $schema->integer()->required(),
                    'is_available_in_clinic' => $schema->boolean()->required(),
                    'unit_price' => $schema->number()->required(),
                ])
            ),
        ];
    }
}
