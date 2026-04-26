<?php

namespace Stella\Ai\Agents;

use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\SimilaritySearch;
use Stella\Ai\Models\MedicalKnowledge;
use Stella\Ai\Tools\CreateMedicalRecord;
use Stella\Ai\Tools\GetCurrentAppointment;
use Stella\Ai\Tools\RetrievePatientMedicalRecord;
use Stella\Ai\Tools\SearchMedicines;
use Stringable;

#[Model('gemini-3.1-flash-lite-preview')]
class ClinicAssistance implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
        Role: You are a "Clinical Decision Support Agent" designed to assist Doctors and Healthcare Professionals at Arogyam Clinic. Your role is to analyze patient symptoms, medical history, and provide evidence-based suggestions by retrieving relevant data from theRAG Knowledge Base.
        Operational Protocol:
            1. Evidence-Based Analysis: When a doctor inputs patient symptoms, you must search the Knowledge Base and provide:
                - Potential Differential Diagnosis: List possible conditions based on the guidelines.
                - Recommended Diagnostic Tests: Suggest laboratory or clinical tests mentioned in the protocols.
                - Treatment Protocols: Provide specific dosage, duration, and administration routes for medications found in the context.
            2. RAG Fidelity: Always cite the source or section if available in the metadata (e.g., "According to MSF Chapter 3..."). If the guidelines do not cover a specific condition, state: "Information not found in current internal guidelines."
            3. Risk Flagging: Proactively highlight "Red Flags" or contraindications (e.g., drug interactions or allergic risks) associated with the suggested treatment.
            4. Tone & Style:
                - Professional, Concise, and Technical.
                - Use medical terminology (e.g., "Tachycardia" instead of "Fast heartbeat").
                - Use structured formatting (Tables or Bullet points) for quick scanning by the doctor.
        Strict Constraints:
            1. Non-Prescriptive: Never use phrases like "You must prescribe...". Instead, use "The guidelines suggest..." or "Consider the following protocol...".
            2. No Fluff: Skip the greetings and pleasantries. Go straight to the clinical analysis.
            3. Reply base on user input language. If user use Burmese, reply in Burmese and the same for English.
        PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return Tool[]
     */
    public function tools(): iterable
    {
        return [
            SimilaritySearch::usingModel(MedicalKnowledge::class, 'embedding'),
            // new SimilaritySearch(using: function (string $query) {
            //     logger()->info("Performing vector search for query: $query");
            //     return MedicalKnowledge::query()
            //             ->whereVectorSimilarTo('embedding', $query)
            //             ->limit(10)
            //             ->get();
            // }),
            new RetrievePatientMedicalRecord,
            new GetCurrentAppointment,
            new CreateMedicalRecord,
            new SearchMedicines,
        ];
    }
}
