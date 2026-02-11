<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: MarketResearchLog (Market Research Log)
 * 
 * Speichert detaillierte Logs aller API-Aufrufe für Debugging
 */
class MarketResearchLog extends Model
{
    use HasFactory;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'market_research_logs';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'watch_id',
        'source',
        'request_data',
        'response_data',
        'processed_result',
        'success',
        'error_message',
        'execution_time',
        'processed_at',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array',
        'processed_result' => 'array',
        'success' => 'boolean',
        'execution_time' => 'decimal:3',
        'processed_at' => 'datetime',
    ];

    /**
     * Relationship: Zugehörige Uhr
     */
    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
