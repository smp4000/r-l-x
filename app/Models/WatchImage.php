<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: WatchImage (Uhrenbild)
 * 
 * Speichert Bilder zu Uhren (5-30 pro Uhr)
 */
class WatchImage extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'watch_images';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'watch_id',
        'filename',
        'path',
        'file_size',
        'mime_type',
        'source',
        'is_primary',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'file_size' => 'integer',
        'is_primary' => 'boolean',
    ];

    /**
     * Relationship: Zugehörige Uhr
     */
    public function watch(): BelongsTo
    {
        return $this->belongsTo(Watch::class);
    }
}
