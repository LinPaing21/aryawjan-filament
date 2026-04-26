<?php

namespace Stella\Ai\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicalKnowledge extends Model
{
    protected $table = \App\Enums\Tables::MEDICAL_KNOWLEDGES->value;

    protected $fillable = ['content', 'embedding', 'metadata'];

    protected $casts = [
        'metadata' => 'array',
        'embedding' => 'array',
    ];
}
