<?php

namespace Stella\Ai\Services;

use Illuminate\Support\Facades\Log;
use Stella\Ai\Agents\TriageAgent;
use Stella\Ai\Models\MedicalKnowledge;
use Stella\Clinic\Models\MedicalRecord;
use Stella\Users\Models\Patient;

class TriageService
{
    /**
     * Analyze patient symptoms using the TriageAgent.
     *
     * @param string|null $userMessage
     * @param int|null $patientId
     * @param mixed $attachment
     * @return array
     */
    public function analyzeSymptoms(?string $userMessage, ?int $patientId = null, mixed $attachment = null, ?string $conversationId = null): array
    {
        try {
            $userMessage = $userMessage ?: 'Please analyze the attached symptoms.';

            // Retrieve relevant knowledge using vector search
            // $knowledges = MedicalKnowledge::query()
            //     ->select('*')
            //     ->whereVectorSimilarTo('embedding', $userMessage, minSimilarity: 0.5)
            //     ->limit(5)
            //     ->pluck('content');

            $patientInfo = '';
            // $ragContent = "RAG Context:\n";
            // foreach ($knowledges as $knowledge) {
            //     $ragContent .= "Content: {$knowledge}\n";
            // }

            $attachments = [];

            if ($attachment) {
                $attachments = $this->prepareAttachments($attachment);
            }

            $agent = new TriageAgent;

            if ($conversationId) {
                $agent = $agent->continue($conversationId, as: auth()->user());
            } else { // First Time
                if ($patientId) {
                    $patient = Patient::with('patientProfile')->find($patientId);
                    if (!$patient) {
                        throw new \Exception("Patient with ID $patientId not found.");
                    }

                    $patientInfo .= "Patient Info:\n- DOB: {$patient->patientProfile->date_of_birth}\n- Gender: {$patient->patientProfile->gender}\n- Blood Type: {$patient->patientProfile->blood_type}\n- Allergies: {$patient->patientProfile->allergies}\n- Chronic Illness: {$patient->patientProfile->chronic_illness}\n";

                    $latestMedicalRecord = MedicalRecord::where('patient_id', $patientId)->latest()->limit(5)->get();
                    $patientInfo .= "Recent Medical Records:\n";
                    foreach ($latestMedicalRecord as $record) {
                        $patientInfo .= "- [{$record->created_at->format('Y-m-d')}] {$record->final_diagnosis} - {$record->triageLog->severity->value} \n";
                    }
                }
                $agent = $agent->forUser(auth()->user());
            }

            // logger("$patientInfo \nUser Message: $userMessage");
            $response = $agent->prompt(
                // "$patientInfo $ragContent \nUser Message: $userMessage",
                "$patientInfo\nUser Message: $userMessage",
                attachments: $attachments
            );
            logger('TriageAgent Response', ['response' => $response]);
            $conversationId = $response->conversationId ?? null;

            return [
                'severity' => $response['severity'],
                'category' => $response['category'],
                'suggested_meds' => $response['suggested_meds'],
                'reasoning' => $response['reasoning'],
                'conversationId' => $conversationId,
            ];
        } catch (\Exception $e) {
            Log::error('TriageService Exception', ['error' => $e->getMessage()]);

            return $this->fallbackErrorResponse();
        }
    }

    /**
     * Prepare attachments for the AI prompt.
     *
     * @param mixed $attachment
     * @return array
     */
    private function prepareAttachments(mixed $attachment): array
    {
        $attachments = [];
        $files = is_array($attachment) ? $attachment : [$attachment];

        foreach ($files as $file) {
            if (empty($file)) {
                continue;
            }

            if ($file instanceof \Livewire\Features\SupportFileUploads\TemporaryUploadedFile) {
                $path = $file->getRealPath();
            } else {
                // In Filament, file upload might give us a path
                // We need to determine if it's an image or document
                $path = storage_path('app/public/' . $file);

                if (! file_exists($path)) {
                    $path = storage_path('app/private/' . $file);
                }

                if (! file_exists($path)) {
                    $path = storage_path('app/' . $file);
                }
            }

            if (! file_exists($path)) {
                continue;
            }

            $mimeType = mime_content_type($path);

            if (str_starts_with($mimeType, 'image/')) {
                $attachments[] = \Laravel\Ai\Files\Image::fromPath($path);
            } else {
                $attachments[] = \Laravel\Ai\Files\Document::fromPath($path);
            }
        }

        return $attachments;
    }

    /**
     * Return a safe fallback response in case of AI failure.
     *
     * @return array
     */
    private function fallbackErrorResponse(): array
    {
        return [
            'severity' => 'high',
            'category' => 'consultation',
            'suggested_meds' => [],
            'reasoning' => 'AI analysis failed. Defaulting to safe consultation. (AI စနစ် အလုပ်မလုပ်သောကြောင့် ဆရာဝန်နှင့် တိုင်ပင်ရန် အကြံပြုပါသည်)',
        ];
    }
}
