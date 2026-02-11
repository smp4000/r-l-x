<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration: Dealer-Beziehungen zu watches hinzufügen
     * 
     * Fügt purchase_dealer_id und selling_dealer_id hinzu
     */
    public function up(): void
    {
        Schema::table('watches', function (Blueprint $table) {
            $table->foreignId('purchase_dealer_id')
                ->nullable()
                ->after('purchase_location')
                ->constrained('dealers')
                ->onDelete('set null')
                ->comment('Gekauft von (Händler)');
                
            $table->foreignId('selling_dealer_id')
                ->nullable()
                ->after('sold_notes')
                ->constrained('dealers')
                ->onDelete('set null')
                ->comment('Verkauft an (Händler)');
        });
    }

    /**
     * Migration rückgängig machen
     */
    public function down(): void
    {
        Schema::table('watches', function (Blueprint $table) {
            $table->dropForeign(['purchase_dealer_id']);
            $table->dropForeign(['selling_dealer_id']);
            $table->dropColumn(['purchase_dealer_id', 'selling_dealer_id']);
        });
    }
};
