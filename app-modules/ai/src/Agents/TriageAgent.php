<?php

namespace Stella\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Tools\SimilaritySearch;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Models\MedicalKnowledge;
use Stella\Ai\Tools\SearchMedicines;
use Stringable;

#[Model('gemini-3.1-flash-lite-preview')]
class TriageAgent implements Agent, HasStructuredOutput, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return <<<PROMPT
        You are an expert AI triage assistant. Your task is to analyze the patient's symptoms based on the provided conversation history and return a strictly structured JSON object.

        Rules:
        1. CONTEXTUAL EVALUATION: Before rejecting, you MUST check the conversation history. If the history contains medical symptoms or health queries, do NOT reject it, even if the current prompt is a request for a summary or analysis.
        2. REJECTION CRITERIA: Only set severity to "low" and provide the "I can only answer health questions" reasoning if the ENTIRE conversation is unrelated to health.
        3. SUMMARY TASK: If the user asks for a 'Clinical Summary Analysis', use the 'reasoning' field to provide that summary in Burmese.
        4. Assess severity (low, medium, high) and category (consultation, pharmacy, emergency) based on the most recent medical evidence in the history.
        5. You MUST return ONLY a valid JSON object.
        6. MEDICINE INVENTORY: Use SearchMedicines to look up relevant medicines. If a medicine is NOT found in the inventory (found_in_inventory: false), still include it in suggested_meds based on clinical guidelines. Inventory availability does NOT affect your clinical recommendations.
        7. CRITICAL: You MUST always populate all required fields — severity, category, suggested_meds, and reasoning — regardless of tool results. Never return an empty or incomplete response.
        PROMPT;
    }

    /**
     * Get the agent's structured output schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'severity' => $schema->string()->enum(Severity::class)->required(),
            'category' => $schema->string()->enum(['consultation', 'pharmacy', 'emergency'])->required(),
            'suggested_meds' => $schema->array()->items($schema->string())->required(),
            'reasoning' => $schema->string()->description('Brief explanation in Burmese')->required(),
        ];
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
            new SearchMedicines,
        ];
    }
}
