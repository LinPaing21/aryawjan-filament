<?php

namespace Stella\Clinic\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\MedicalRecord;
use Stella\Clinic\Enums\AppointmentStatus;

class Appointment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'patient_id',
        'doctor_id',
        'triage_log_id',
        'token_number',
        'scheduled_at',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'scheduled_at' => 'date:Y-m-d',
            'status' => AppointmentStatus::class,
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function triageLog(): BelongsTo
    {
        return $this->belongsTo(TriageLog::class, 'triage_log_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'triage_log_id', 'triage_log_id');
    }

    public function getScheduledAtAttribute($value)
    {
        return Carbon::parse($value)->format('Y-m-d');
    }
}
