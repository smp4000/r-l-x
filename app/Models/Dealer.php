<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model: Dealer (Kontakte: Händler, Juweliere, Privatpersonen)
 * 
 * CRM für Kauf- und Verkaufskontakte
 */
class Dealer extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'dealers';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'user_id',
        'name',
        'company_name',
        'firstname',
        'lastname',
        'email',
        'phone',
        'street',
        'street_number',
        'zip',
        'city',
        'country',
        'contact_type',
        'is_buyer',
        'is_seller',
        'notes',
        'tags',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'is_buyer' => 'boolean',
        'is_seller' => 'boolean',
        'tags' => 'array',
    ];

    /**
     * Relationship: Eigentümer des Kontakts  
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Gekaufte Uhren (von diesem Händler)
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(Watch::class, 'purchase_location', 'name');
    }

    /**
     * Relationship: Verkaufte Uhren (an diesen Händler)
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Watch::class, 'sold_to_dealer_id');
    }

    /**
     * Relationship: Service-Einträge
     */
    public function watchServices(): HasMany
    {
        return $this->hasMany(WatchService::class);
    }
}
