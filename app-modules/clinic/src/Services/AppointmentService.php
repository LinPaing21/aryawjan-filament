<?php

namespace Stella\Clinic\Services;

use Carbon\Carbon;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Models\DoctorSchedule;

class AppointmentService
{
    public function calculateTokenNumber($date, int $doctorId)
    {
        // Logic to calculate the next token number based on the current date and existing appointments
        $count = Appointment::whereDate('scheduled_at', $date)->where('doctor_id', $doctorId)->count();
        $convertedDay = Carbon::parse($date)->format('D');
        $maxPatient = DoctorSchedule::where('doctor_id', $doctorId)
            ->whereJsonContains('days', $convertedDay)
            ->sum('max_patients');

        if ($maxPatient === 0) {
            throw new \Exception('Doctor is not available on this day.');
        }

        if ($count >= $maxPatient) {
            throw new \Exception('Maximum number of patients reached for this day.');
        }

        return Carbon::parse($date)->format('yMd') . '-' . $doctorId .'-' . str_pad($count + 1, strlen((string) $maxPatient), '0', STR_PAD_LEFT);
    }

    public function getUnavailableDates($doctorId, $limitMonths = 1)
    {
        $unavailableDates = [];

        $schedules = DoctorSchedule::where('doctor_id', $doctorId)->get();

        foreach ($schedules as $schedule) {
            $days = $schedule->days;
            $startDate = Carbon::now();
            $endDate = Carbon::now()->addMonths($limitMonths);

            while ($startDate->lte($endDate)) {
                if (!in_array($startDate->format('D'), $days)) {
                    $unavailableDates[] = $startDate->toDateString();
                } elseif (in_array($startDate->toDateString(), $unavailableDates)) {
                    $unavailableDates = array_diff($unavailableDates, [$startDate->toDateString()]);
                }
                $startDate->addDay();
            }
        }

        return array_unique($unavailableDates);
    }
}
