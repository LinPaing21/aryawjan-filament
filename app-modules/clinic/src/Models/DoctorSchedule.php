<?php

namespace Stella\Clinic\Models;

use Illuminate\Database\Eloquent\Model;

class DoctorSchedule extends Model
{
    protected $fillable = [
        'doctor_id',
        'days',
        'start_time',
        'end_time',
        'max_patients',
    ];

    protected function casts(): array
    {
        return [
            'days' => 'array',
        ];
    }

    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'doctor_id');
    }
}
