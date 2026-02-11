<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: User-API-Einstellungen-Tabelle erstellen
     * 
     * Speichert verschlüsselte API-Keys pro User
     */
    public function up(): void
    {
        Schema::create('user_api_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade')->comment('User');
            
            // API Keys (verschlüsselt)
            $table->text('perplexity_api_key')->nullable()->comment('Perplexity AI API Key (encrypted)');
            $table->text('openai_api_key')->nullable()->comment('OpenAI API Key (encrypted)');
            $table->text('google_search_api_key')->nullable()->comment('Google Custom Search API Key (encrypted)');
            $table->string('google_search_engine_id')->nullable()->comment('Google Search Engine ID (encrypted)');
            
            $table->timestamps();
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('user_api_settings');
    }
};
