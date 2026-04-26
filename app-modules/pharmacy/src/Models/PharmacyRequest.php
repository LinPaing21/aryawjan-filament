<?php

namespace Stella\Pharmacy\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stella\Ai\Models\TriageLog;
use Stella\Clinic\Models\MedicalRecord;

class PharmacyRequest extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(function (PharmacyRequest $request) {
            if ($request->isDirty('status') && $request->status === 'approved') {
                $medicines = $request->approved_medicines ?? $request->suggested_medicines ?? [];

                $prescriptions = array_map(function ($medicine) {
                    return [
                        'drug_name' => $medicine,
                        'dosage' => null,
                        'is_available_in_clinic' => true,
                        'unit_price' => null,
                        'quantity' => 1,
                    ];
                }, $medicines);

                MedicalRecord::create([
                    'patient_id' => $request->patient_id,
                    'doctor_id' => $request->pharmacist_id ?? $request->patient_id, // Fallback safely
                    'triage_log_id' => $request->triage_log_id,
                    'symptoms' => $request->triageLog ? $request->triageLog->raw_symptoms : 'Not Available',
                    'final_diagnosis' => $request->triageLog ? $request->triageLog->ai_analysis['reasoning'] : 'Not Available',
                    'prescriptions' => $prescriptions,
                ]);

                logger('done');
            }
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'triage_log_id',
        'patient_id',
        'pharmacist_id',
        'suggested_medicines',
        'approved_medicines',
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
            'suggested_medicines' => 'array',
            'approved_medicines' => 'array',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function triageLog(): BelongsTo
    {
        return $this->belongsTo(TriageLog::class, 'triage_log_id');
    }
}
