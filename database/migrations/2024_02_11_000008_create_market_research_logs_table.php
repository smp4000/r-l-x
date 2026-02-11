<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Market-Research-Logs-Tabelle erstellen (Debug-Logs)
     * 
     * Speichert detaillierte Logs aller API-Aufrufe für Debugging
     */
    public function up(): void
    {
        Schema::create('market_research_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_id')->constrained()->onDelete('cascade')->comment('Zugehörige Uhr');
            
            $table->enum('source', ['perplexity_ai', 'python_script', 'chrono24_scraper'])->comment('Datenquelle');
            $table->json('request_data')->nullable()->comment('Request-Daten (JSON)');
            $table->json('response_data')->nullable()->comment('Response-Daten (JSON)');
            $table->json('processed_result')->nullable()->comment('Verarbeitetes Ergebnis (JSON)');
            
            $table->boolean('success')->default(false)->comment('Erfolgreich?');
            $table->text('error_message')->nullable()->comment('Fehlermeldung');
            $table->decimal('execution_time', 5, 3)->nullable()->comment('Ausführungszeit in Sekunden');
            
            $table->timestamp('processed_at')->comment('Zeitpunkt der Verarbeitung');
            $table->timestamps();

            // Indizes
            $table->index('watch_id');
            $table->index('source');
            $table->index('processed_at');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('market_research_logs');
    }
};
