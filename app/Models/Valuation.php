<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: Valuation (Bewertung/Marktwert)
 * 
 * Speichert Historie der Marktwert-Ermittlungen
 */
class Valuation extends Model
{
    use HasFactory;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'valuations';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'watch_id',
        'source',
        'estimated_value',
        'median_price',
        'average_price',
        'price_range',
        'comparable_listings',
        'valuated_at',
        'notes',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'estimated_value' => 'decimal:2',
        'median_price' => 'decimal:2',
        'average_price' => 'decimal:2',
        'price_range' => 'array',
        'comparable_listings' => 'integer',
        'valuated_at' => 'datetime',
    ];

    /**
     * Relationship: Zugehörige Uhr
     */
    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
