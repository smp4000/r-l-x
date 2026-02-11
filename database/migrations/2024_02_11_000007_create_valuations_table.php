<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Bewertungen-Tabelle erstellen (Marktwert-Historie)
     * 
     * Speichert Historie der Marktwert-Ermittlungen
     */
    public function up(): void
    {
        Schema::create('valuations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_id')->constrained()->onDelete('cascade')->comment('Zugehörige Uhr');
            
            $table->enum('source', ['manual', 'perplexity_ai', 'chrono24_api'])->default('manual')->comment('Quelle der Bewertung');
            $table->decimal('estimated_value', 10, 2)->comment('Geschätzter Wert in €');
            $table->decimal('median_price', 10, 2)->nullable()->comment('Median-Preis');
            $table->decimal('average_price', 10, 2)->nullable()->comment('Durchschnittspreis');
            $table->json('price_range')->nullable()->comment('Preisspanne {min, max}');
            $table->integer('comparable_listings')->default(0)->comment('Anzahl vergleichbarer Angebote');
            
            $table->timestamp('valuated_at')->comment('Zeitpunkt der Bewertung');
            $table->text('notes')->nullable()->comment('Zusätzliche Informationen (JSON)');
            
            $table->timestamps();

            // Indizes
            $table->index('watch_id');
            $table->index('valuated_at');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('valuations');
    }
};
