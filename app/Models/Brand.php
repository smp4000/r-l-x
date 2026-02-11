<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: Brand (Uhrenmarke)
 * 
 * Repräsentiert eine Uhrenmarke (z.B. Rolex, Omega, Patek Philippe)
 */
class Brand extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'brands';

    /**
     * Massenzuw eisbare Attribute
     */
    protected $fillable = [
        'name',
        'logo',
        'country',
        'founded_year',
        'is_active',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'founded_year' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Uhren dieser Marke
     */
    public function watches(): HasMany
    {
        return $this->hasMany(Watch::class);
    }
}
