<?php

namespace Stella\Users\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DoctorProfile extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
