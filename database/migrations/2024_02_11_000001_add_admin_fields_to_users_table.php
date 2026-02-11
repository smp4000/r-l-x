<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Admin-Felder zu Users-Tabelle hinzufügen
     * 
     * Erweitert die bestehende users-Tabelle um Admin-Status und Aktivierungs-Flag
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false)->after('email');
            $table->boolean('is_active')->default(true)->after('is_admin');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_admin', 'is_active']);
        });
    }
};
