<?php

namespace Stella\Clinic\Livewire;

use App\Models\User;
use Livewire\Component;
use Stella\Clinic\Enums\AppointmentStatus;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Models\DoctorSchedule;

class PublicQueueBoard extends Component
{
    /**
     * @return list<array{doctor: User, current: ?Appointment, next: ?Appointment, remaining: int}>
     */
    public function getActiveDoctorQueues(): array
    {
        $today = now()->format('D');
        $currentTime = now()->format('H:i:s');

        $schedules = DoctorSchedule::with('doctor')
            ->whereJsonContains('days', $today)
            ->where('start_time', '<=', $currentTime)
            ->where('end_time', '>=', $currentTime)
            ->get();

        return $schedules->map(function (DoctorSchedule $schedule): array {
            $queue = Appointment::selectRaw("*, split_part(token_number, '-', -1) as token_order")
                ->where('doctor_id', $schedule->doctor_id)
                ->whereDate('scheduled_at', today())
                ->whereIn('status', [AppointmentStatus::Booked, AppointmentStatus::ReQueued])
                ->orderByRaw('CASE status WHEN ? THEN 0 ELSE 1 END', [AppointmentStatus::ReQueued->value])
                ->orderBy('token_number')
                ->get();

            return [
                'doctor' => $schedule->doctor,
                'current' => $queue->first(),
                'next' => $queue->skip(1)->first(),
                'remaining' => $queue->count(),
            ];
        })->all();
    }

    public function render(): \Illuminate\View\View
    {
        return view('clinic::livewire.public-queue-board')
            ->layout('clinic::layouts.queue-board');
    }
}
