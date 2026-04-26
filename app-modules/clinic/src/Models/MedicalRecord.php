<?php

namespace Stella\Clinic\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\Appointment;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'ai_suggestions' => 'array',
        'prescriptions' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function triageLog()
    {
        return $this->belongsTo(TriageLog::class, 'triage_log_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'triage_log_id', 'triage_log_id');
    }
}
