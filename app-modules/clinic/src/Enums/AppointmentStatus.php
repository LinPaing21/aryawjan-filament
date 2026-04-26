<?php

namespace Stella\Clinic\Enums;

enum AppointmentStatus: string
{
    case Booked = 'booked';

    case Missed = 'missed';

    case ReQueued = 're_queued';

    case Completed = 'completed';

    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Booked => 'Booked',
            self::Missed => 'Missed',
            self::ReQueued => 'Re-queued',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Booked => 'warning',
            self::Missed => 'danger',
            self::ReQueued => 'info',
            self::Completed => 'success',
            self::Cancelled => 'gray',
        };
    }
}
