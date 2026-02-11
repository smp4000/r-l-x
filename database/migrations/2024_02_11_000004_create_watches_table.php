<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Uhren-Tabelle erstellen (HAUPTTABELLE)
     * 
     * Speichert alle Details zu Uhren in der Sammlung, Wunschliste oder verkauft
     */
    public function up(): void
    {
        Schema::create('watches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Eigentümer');
            $table->foreignId('brand_id')->nullable()->constrained()->onDelete('restrict')->comment('Marke');
            $table->enum('ownership_status', ['in_besitz', 'wunschliste', 'verkauft'])->default('wunschliste')->comment('Status');
            $table->boolean('is_public')->default(false)->comment('Für öffentliches Profil sichtbar?');
            
            // Basis-Daten
            $table->string('model')->comment('Modellname');
            $table->string('reference_number')->comment('Referenznummer');
            $table->string('serial_number')->nullable()->comment('Seriennummer');
            $table->text('description')->nullable()->comment('Beschreibung');
            
            // Gehäuse
            $table->string('case_material')->nullable()->comment('Material Gehäuse (z.B. Edelstahl)');
            $table->decimal('case_diameter', 5, 2)->nullable()->comment('Durchmesser in mm');
            $table->decimal('case_height', 5, 2)->nullable()->comment('Höhe in mm');
            $table->string('bezel_material')->nullable()->comment('Material Lünette');
            $table->string('crystal_type')->nullable()->comment('Glas-Typ (z.B. Saphirglas)');
            $table->string('water_resistance')->nullable()->comment('Wasserdichtigkeit (z.B. 100m)');
            
            // Zifferblatt
            $table->string('dial_color')->nullable()->comment('Farbe Zifferblatt');
            $table->string('dial_numerals')->nullable()->comment('Zahlen auf Zifferblatt');
            
            // Armband
            $table->string('bracelet_material')->nullable()->comment('Material Armband');
            $table->string('bracelet_color')->nullable()->comment('Farbe Armband');
            $table->string('clasp_material')->nullable()->comment('Material Schließe');
            $table->string('clasp_type')->nullable()->comment('Typ Schließe');
            
            // Uhrwerk
            $table->string('movement_type')->nullable()->comment('Aufzug (z.B. Automatik)');
            $table->string('caliber')->nullable()->comment('Kaliber/Werk');
            $table->string('base_caliber')->nullable()->comment('Basiskaliber');
            $table->integer('power_reserve')->nullable()->comment('Gangreserve in Stunden');
            $table->integer('jewels')->nullable()->comment('Anzahl Steine');
            $table->string('frequency')->nullable()->comment('Frequenz/Schwingung');
            $table->json('functions')->nullable()->comment('Funktionen (Array)');
            
            // Sonstiges
            $table->enum('gender', ['Herrenuhr', 'Damenuhr', 'Herrenuhr/Unisex'])->nullable()->comment('Geschlecht');
            $table->text('delivery_scope')->nullable()->comment('Lieferumfang');
            
            // Kaufdetails
            $table->decimal('purchase_price', 10, 2)->nullable()->comment('Kaufpreis in €');
            $table->date('purchase_date')->nullable()->comment('Kaufdatum');
            $table->string('purchase_location')->nullable()->comment('Kaufort (Name)');
            $table->enum('condition', ['neu', 'ungetragen', 'getragen', 'stark_getragen'])->default('getragen')->comment('Zustand');
            $table->boolean('box_available')->default(false)->comment('Box vorhanden?');
            $table->boolean('papers_available')->default(false)->comment('Papiere vorhanden?');
            
            // Verkaufsdetails
            $table->date('sold_at')->nullable()->comment('Verkaufsdatum');
            $table->decimal('sold_price', 10, 2)->nullable()->comment('Verkaufspreis');
            $table->foreignId('sold_to_dealer_id')->nullable()->constrained('dealers')->onDelete('set null')->comment('Käufer (Dealer)');
            $table->text('sold_notes')->nullable()->comment('Verkaufsnotizen');
            
            // Versicherung
            $table->string('insurance_company')->nullable()->comment('Versicherungsgesellschaft');
            $table->string('insurance_policy_number')->nullable()->comment('Versicherungsnummer');
            $table->decimal('insurance_value', 10, 2)->nullable()->comment('Versicherungswert');
            $table->date('insurance_valid_until')->nullable()->comment('Versicherung gültig bis');
            $table->text('insurance_notes')->nullable()->comment('Versicherungsnotizen');
            
            // Bewertung
            $table->decimal('current_market_value', 10, 2)->nullable()->comment('Aktueller Marktwert');
            $table->date('last_valuation_at')->nullable()->comment('Letzte Bewertung am');
            
            // Limitierte Edition
            $table->boolean('is_limited_edition')->default(false)->comment('Limitiertes Sondermodell?');
            $table->integer('limited_edition_number')->nullable()->comment('Limitierungs-Nummer');
            $table->integer('limited_edition_total')->nullable()->comment('Gesamt-Auflage');
            
            // Sonstiges
            $table->string('storage_location')->nullable()->comment('Aufbewahrungsort');
            $table->string('owner_name')->nullable()->comment('Abweichender Eigentümer (Name)');
            $table->text('owner_address')->nullable()->comment('Abweichender Eigentümer (Adresse)');
            $table->text('notes')->nullable()->comment('Notizen');
            
            // AI-Daten
            $table->json('ai_fetched_data')->nullable()->comment('Rohdaten von KI-Abfrage');
            
            $table->timestamps();
            $table->softDeletes();

            // Indizes
            $table->unique('reference_number');
            $table->index('user_id');
            $table->index('brand_id');
            $table->index('ownership_status');
            $table->index('is_public');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('watches');
    }
};
