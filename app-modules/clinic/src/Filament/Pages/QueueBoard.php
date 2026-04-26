<?php

namespace Stella\Clinic\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Stella\Clinic\Enums\AppointmentStatus;
use Stella\Clinic\Models\Appointment;
use Stella\Clinic\Models\DoctorSchedule;
use UnitEnum;

class QueueBoard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::QueueList;

    protected static ?string $title = 'Queue Board';

    protected static ?string $slug = 'queue-board';

    protected static string|UnitEnum|null $navigationGroup = 'Clinic';

    protected static ?int $navigationSort = 10;

    protected string $view = 'clinic::filament.pages.queue-board';

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
            // re_queued patients are served first (they already waited), then booked by token order
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
}
