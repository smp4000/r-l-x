<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Kontakte-Tabelle erstellen (Händler, Juweliere, Privatpersonen)
     * 
     * CRM für Kauf- und Verkaufskontakte
     */
    public function up(): void
    {
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Eigentümer des Kontakts');
            
            // Basis-Kontaktdaten
            $table->string('name')->comment('Vollständiger Name oder Firmenname');
            $table->string('company_name')->nullable()->comment('Firmenname (falls Firma)');
            $table->string('firstname')->nullable()->comment('Vorname (falls Privatperson)');
            $table->string('lastname')->nullable()->comment('Nachname (falls Privatperson)');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Adresse
            $table->string('street')->nullable();
            $table->string('street_number', 50)->nullable();
            $table->string('zip', 20)->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            
            // Typ und Flags
            $table->enum('contact_type', ['dealer', 'jeweler', 'private_person'])->default('dealer')->comment('Kontakttyp');
            $table->boolean('is_buyer')->default(false)->comment('Kauft Uhren an?');
            $table->boolean('is_seller')->default(false)->comment('Verkauft Uhren?');
            
            // CRM-Felder
            $table->text('notes')->nullable()->comment('Notizen');
            $table->json('tags')->nullable()->comment('Tags für Kategorisierung');
            
            $table->timestamps();
            $table->softDeletes();

            // Indizes
            $table->index('user_id');
            $table->index('email');
            $table->index('contact_type');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
    }
};
