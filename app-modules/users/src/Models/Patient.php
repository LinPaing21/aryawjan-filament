<?php

namespace Stella\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class Patient extends User
{
    protected $table = 'users';

    protected string $guard_name = 'web';

    protected static function booted()
    {
        static::addGlobalScope('patient', function (Builder $builder) {
            $builder->whereHas('roles', function (Builder $query) {
                $query->where('name', 'Patient');
            });
        });

        static::created(function (Patient $patient) {
            if (!$patient->hasRole('Patient')) {
                $patient->assignRole('Patient');
            }
        });
    }

    public function patientProfile()
    {
        return $this->hasOne(\Stella\Users\Models\PatientProfile::class, 'user_id');
    }
}
