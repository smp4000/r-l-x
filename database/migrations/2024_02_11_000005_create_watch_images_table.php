<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Uhrenbilder-Tabelle erstellen
     * 
     * Speichert Bilder zu Uhren (5-30 pro Uhr)
     */
    public function up(): void
    {
        Schema::create('watch_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('watch_id')->constrained()->onDelete('cascade')->comment('Zugehörige Uhr');
            
            $table->string('filename')->comment('Dateiname');
            $table->string('path')->comment('Speicherpfad');
            $table->bigInteger('file_size')->nullable()->comment('Dateigröße in Bytes');
            $table->string('mime_type', 100)->nullable()->comment('MIME-Typ');
            
            $table->enum('source', ['user_upload', 'manufacturer', 'ai_fetched'])->default('user_upload')->comment('Bildquelle');
            $table->boolean('is_primary')->default(false)->comment('Hauptbild?');
            
            $table->timestamps();
            $table->softDeletes();

            // Indizes
            $table->index('watch_id');
            $table->index('is_primary');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::dropIfExists('watch_images');
    }
};
