<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();

            // 1. LÉPÉS: Létrehozzuk az oszlopot STRING-ként
            $table->string('sector_id');

            // 2. LÉPÉS: Kézzel hozzákötjük a sectors tábla id oszlopához
            $table->foreign('sector_id')->references('id')->on('sectors')->onDelete('cascade');

            // A game_id maradhat foreignId, mert az biztosan SZÁM (BigInt)
            $table->foreignId('game_id')->constrained('games')->onDelete('cascade');

            $table->integer('row');
            $table->integer('col');
            $table->tinyInteger('status')->default(0);

            // Egyedi kulcs és indexek
            $table->unique(['game_id', 'sector_id', 'row', 'col']);
            $table->index(['game_id', 'sector_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seats');
    }
};
