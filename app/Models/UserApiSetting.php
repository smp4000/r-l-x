<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: UserApiSetting (User-API-Einstellungen)
 * 
 * Speichert verschlüsselte API-Keys pro User
 */
class UserApiSetting extends Model
{
    use HasFactory;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'user_api_settings';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'user_id',
        'perplexity_api_key',
        'openai_api_key',
        'google_search_api_key',
        'google_search_engine_id',
    ];

    /**
     * Casting von Attributen (verschlüsselt)
     */
    protected $casts = [
        'perplexity_api_key' => 'encrypted',
        'openai_api_key' => 'encrypted',
        'google_search_api_key' => 'encrypted',
        'google_search_engine_id' => 'encrypted',
    ];

    /**
     * Hidden attributes
     */
    protected $hidden = [
        'perplexity_api_key',
        'openai_api_key',
        'google_search_api_key',
        'google_search_engine_id',
    ];

    /**
     * Relationship: User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
