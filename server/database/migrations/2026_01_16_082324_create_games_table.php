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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_home_id')->constrained(table: 'teams')->onDelete('restrict')->nullable();
            $table->foreignId('team_away_id')->constrained('teams')->onDelete('restrict')->nullable(); 
            $table->dateTime('game_date')->nullable();
            $table->unique(['team_home_id', 'team_away_id', 'game_date']);
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
