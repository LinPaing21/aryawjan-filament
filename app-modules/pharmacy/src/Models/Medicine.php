<?php

namespace Stella\Pharmacy\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stella\Pharmacy\Database\Factories\MedicineFactory;

class Medicine extends Model
{
    /** @use HasFactory<MedicineFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'original_price',
        'sell_price',
        'stock_quantity',
        'is_prescription_only',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'original_price' => 'decimal:2',
            'sell_price' => 'decimal:2',
            'stock_quantity' => 'integer',
            'is_prescription_only' => 'boolean',
        ];
    }
}
