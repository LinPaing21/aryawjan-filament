<?php

namespace Stella\Ai\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stella\Ai\Enums\Severity;
use Stella\Ai\Enums\TriageStatus;
use Stella\Clinic\Models\Appointment;
use Stella\Pharmacy\Models\PharmacyRequest;
use Stella\Users\Models\Patient;

class TriageLog extends Model
{
    protected $fillable = ['patient_id', 'raw_symptoms', 'attachments', 'ai_analysis', 'severity', 'status'];

    protected $casts = [
        'attachments' => 'array',
        'ai_analysis' => 'array',
        'severity' => Severity::class,
        'status' => TriageStatus::class
    ];

    /**
     * Get the patient that owns the TriageLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function pharmacyRequest()
    {
        return $this->hasOne(PharmacyRequest::class, 'triage_log_id');
    }

    public function appointment()
    {
        return $this->hasOne(Appointment::class, 'triage_log_id');
    }
}
