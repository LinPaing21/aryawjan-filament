<?php

namespace Stella\Ai\Enums;

enum TriageStatus: string
{
    case PENDING = 'pending';

    case TO_DOCTOR = 'to_doctor';

    case TO_PHARMACY = 'to_pharmacy';
}
