<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Clinic\Models\Appointment;
use Stringable;

class GetAppointmentStats implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Retrieves appointment statistics for the clinic. Returns counts by status (booked, completed, cancelled), breakdown per doctor, and total counts. Optionally filter by start_date and end_date (YYYY-MM-DD format). Use this to understand patient flow, doctor workloads, and appointment trends.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $query = Appointment::query()->with('doctor:id,name');

        if (! empty($request['start_date'])) {
            $query->whereDate('scheduled_at', '>=', $request['start_date']);
        }

        if (! empty($request['end_date'])) {
            $query->whereDate('scheduled_at', '<=', $request['end_date']);
        }

        $appointments = $query->get();

        $statusBreakdown = $appointments
            ->groupBy('status')
            ->map(fn ($group) => $group->count())
            ->toArray();

        $doctorBreakdown = $appointments
            ->groupBy('doctor_id')
            ->map(fn ($group) => [
                'doctor_name' => $group->first()->doctor?->name ?? 'Unknown',
                'total' => $group->count(),
                'by_status' => $group->groupBy('status')->map(fn ($s) => $s->count())->toArray(),
            ])
            ->values()
            ->toArray();

        $stats = [
            'period' => [
                'start_date' => $request['start_date'] ?? 'all time',
                'end_date' => $request['end_date'] ?? 'all time',
            ],
            'total_appointments' => $appointments->count(),
            'by_status' => $statusBreakdown,
            'by_doctor' => $doctorBreakdown,
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
