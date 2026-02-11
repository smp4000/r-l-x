<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Marken-Tabelle erstellen
     * 
     * Speichert Uhrenmarken (z.B. Rolex, Omega, Patek Philippe)
     */
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Markenname (z.B. Rolex)');
            $table->string('logo')->nullable()->comment('Pfad zum Logo (Storage)');
            $table->string('country')->nullable()->comment('Herkunftsland');
            $table->integer('founded_year')->nullable()->comment('Gründungsjahr');
            $table->boolean('is_active')->default(true)->comment('Ist Marke aktiv?');
            $table->timestamps();
            $table->softDeletes();

            // Indizes
            $table->index('name');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
