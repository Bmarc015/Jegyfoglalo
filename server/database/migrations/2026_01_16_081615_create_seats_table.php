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
        
        // A foreignId alapból Unsigned Big Integer-t csinál
        $table->foreignId('sector_id')->constrained('sectors')->onDelete('cascade');
        
        // Próbáld meg így, ez a legmodernebb Laravel szintaktika
        $table->foreignId('game_id')->constrained('games')->onDelete('cascade');
        
        $table->integer('row');
        $table->integer('col');
        $table->tinyInteger('status')->default(0);

        $table->unique(['game_id', 'sector_id', 'row', 'col']);
        $table->index(['game_id', 'sector_id']);
        $table->timestamps();
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
