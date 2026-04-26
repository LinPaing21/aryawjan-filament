<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Ai\Models\TriageLog;
use Stringable;

class GetTriageStats implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieves triage statistics including severity distribution (low, medium, high) and outcome breakdown (pending, to_doctor, to_pharmacy). Optionally filter by start_date and end_date (YYYY-MM-DD format). Use this to understand patient case severity trends and triage outcomes.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = TriageLog::query();

        if (! empty($request['start_date'])) {
            $query->whereDate('created_at', '>=', $request['start_date']);
        }

        if (! empty($request['end_date'])) {
            $query->whereDate('created_at', '<=', $request['end_date']);
        }

        $logs = $query->get();

        $bySeverity = $logs
            ->groupBy(fn ($log) => $log->severity?->value ?? 'unknown')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $byStatus = $logs
            ->groupBy(fn ($log) => $log->status?->value ?? 'unknown')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $stats = [
            'period' => [
                'start_date' => $request['start_date'] ?? 'all time',
                'end_date' => $request['end_date'] ?? 'all time',
            ],
            'total_triage_logs' => $logs->count(),
            'by_severity' => $bySeverity,
            'by_status' => $byStatus,
        ];

        return json_encode($stats);
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'start_date' => $schema->string(),
            'end_date' => $schema->string(),
        ];
    }
}
