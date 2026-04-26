<?php

namespace App\Enums;

enum Tables: string
{
    case USERS = 'users';

    case MEDICAL_KNOWLEDGES = 'medical_knowledges';

    case TRIAGE_LOGS = 'triage_logs';

    case MEDICAL_RECORDS = 'medical_records';

    case INVOICES = 'invoices';

}
