<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Clinic\Models\MedicalRecord;
use Stringable;

class RetrievePatientMedicalRecord implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'This tool retrieves the latest medical records of a patient based on their ID. It returns the most recent 5 medical records for the specified patient.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        return (string) MedicalRecord::where('patient_id', $request['patientId'])->latest()->limit(5)->get();

    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'patientId' => $schema->integer()->required(),
        ];
    }
}
