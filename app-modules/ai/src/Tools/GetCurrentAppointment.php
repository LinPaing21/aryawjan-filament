<?php

namespace Stella\Ai\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stella\Clinic\Models\Appointment;
use Stringable;

class GetCurrentAppointment implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'This tool retrieves the current appointment details including patient information.';
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $app = (string) Appointment::with(['triageLog', 'patient'])->where('doctor_id', auth()->id())->whereDate('scheduled_at', ">=", today())->where('status', 'booked')->first();
        logger()->info('GetCurrentAppointment result: ' . $app);
        return $app;
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
