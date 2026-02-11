<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Model: Watch (Uhr)
 * 
 * Hauptmodel für Uhren in der Sammlung, Wunschliste oder verkauft
 */
class Watch extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Datenbank-Tabelle
     */
    protected $table = 'watches';

    /**
     * Massenzuweisbare Attribute
     */
    protected $fillable = [
        'user_id',
        'brand_id',
        'ownership_status',
        'is_public',
        'model',
        'reference_number',
        'serial_number',
        'description',
        'case_material',
        'case_diameter',
        'case_height',
        'bezel_material',
        'crystal_type',
        'water_resistance',
        'dial_color',
        'dial_numerals',
        'bracelet_material',
        'bracelet_color',
        'clasp_material',
        'clasp_type',
        'movement_type',
        'caliber',
        'base_caliber',
        'power_reserve',
        'jewels',
        'frequency',
        'functions',
        'gender',
        'delivery_scope',
        'purchase_price',
        'purchase_date',
        'purchase_location',
        'condition',
        'box_available',
        'papers_available',
        'sold_at',
        'sold_price',
        'sold_to_dealer_id',
        'sold_notes',
        'insurance_company',
        'insurance_policy_number',
        'insurance_value',
        'insurance_valid_until',
        'insurance_notes',
        'current_market_value',
        'last_valuation_at',
        'is_limited_edition',
        'limited_edition_number',
        'limited_edition_total',
        'storage_location',
        'owner_name',
        'owner_address',
        'notes',
        'ai_fetched_data',
    ];

    /**
     * Casting von Attributen
     */
    protected $casts = [
        'is_public' => 'boolean',
        'case_diameter' => 'decimal:2',
        'case_height' => 'decimal:2',
        'power_reserve' => 'integer',
        'jewels' => 'integer',
        'functions' => 'array',
        'purchase_price' => 'decimal:2',
        'purchase_date' => 'date',
        'box_available' => 'boolean',
        'papers_available' => 'boolean',
        'sold_at' => 'date',
        'sold_price' => 'decimal:2',
        'insurance_value' => 'decimal:2',
        'insurance_valid_until' => 'date',
        'current_market_value' => 'decimal:2',
        'last_valuation_at' => 'date',
        'is_limited_edition' => 'boolean',
        'limited_edition_number' => 'integer',
        'limited_edition_total' => 'integer',
        'ai_fetched_data' => 'array',
    ];

    /**
     * Relationship: Eigentümer
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship: Marke
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Relationship: Verkauft an (Händler)
     */
    public function soldToDealer(): BelongsTo
    {
        return $this->belongsTo(Dealer::class, 'sold_to_dealer_id');
    }

    /**
     * Relationship: Bilder
     */
    public function images(): HasMany
    {
        return $this->hasMany(WatchImage::class);
    }

    /**
     * Relationship: Hauptbild (PRIMARY)
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(WatchImage::class)->where('is_primary', true);
    }

    /**
     * Relationship: Dokumente (in Phase 2)
     */
    public function documents(): HasMany
    {
        return $this->hasMany(WatchDocument::class);
    }

    /**
     * Relationship: Service-Einträge (in Phase 2)
     */
    public function services(): HasMany
    {
        return $this->hasMany(WatchService::class);
    }

    /**
     * Relationship: Bewertungen (Marktwert-Historie)
     */
    public function valuations(): HasMany
    {
        return $this->hasMany(Valuation::class);
    }

    /**
     * Relationship: Market Research Logs
     */
    public function researchLogs(): HasMany
    {
        return $this->hasMany(MarketResearchLog::class);
    }

    /**
     * Relationship: Verkaufsangebot (in Phase 3)
     */
    public function saleOffer(): HasOne
    {
        return $this->hasOne(SaleOffer::class);
    }

    /**
     * Scope: Nur Uhren in Besitz
     */
    public function scopeInBesitz(Builder $query): Builder
    {
        return $query->where('ownership_status', 'in_besitz');
    }

    /**
     * Scope: Nur Wunschliste
     */
    public function scopeWunschliste(Builder $query): Builder
    {
        return $query->where('ownership_status', 'wunschliste');
    }

    /**
     * Scope: Nur verkaufte Uhren
     */
    public function scopeVerkauft(Builder $query): Builder
    {
        return $query->where('ownership_status', 'verkauft');
    }

    /**
     * Scope: Nur öffentliche Uhren
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }
}
