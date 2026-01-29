<?php

namespace Tests\Unit;

use App\Models\Team;
use App\Models\Sector;
use App\Models\Seat;
use App\Models\Game;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Táblák létezésének ellenőrzése
     */
    public function test_all_tables_exist()
    {
        $tables = ['sectors', 'teams', 'seats', 'games', 'tickets', 'users'];
        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "A(z) {$table} tábla hiányzik.");
        }
    }

    /**
     * Szektor és Szék kapcsolat/index tesztelése
     */
    public function test_sector_and_seat_relationship()
    {
        $sector = Sector::create([
            'sector_number' => 101,
            'sector_price' => 12500.50
        ]);

        $seat = Seat::create([
            'sector_id' => $sector->id,
            'seat_number' => 1,
            'row' => 5,
            'col' => 10
        ]);

        // Modell szintű kapcsolat tesztelése (Seat belongsTo Sector)
        $this->assertEquals($sector->sector_number, $seat->sector->sector_number);

        // Adatbázis szintű összetett egyediség tesztelése
        $this->expectException(\Illuminate\Database\QueryException::class);
        Seat::create([
            'sector_id' => $sector->id,
            'seat_number' => 2, // Másik székszám
            'row' => 5,         // De ugyanaz a sor
            'col' => 10         // És ugyanaz az oszlop -> ütköznie kell
        ]);
    }

    /**
     * Meccs és Csapatok kapcsolatának tesztelése
     */
    public function test_game_team_constraints()
    {
        $home = Team::create(['team_name' => 'Újszász VVSE', 'team_city' => 'Újszász']);
        $away = Team::create(['team_name' => 'Szolnok MÁV', 'team_city' => 'Szolnok']);

        $date = '2026-03-15 16:00:00';

        $game = Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => $date
        ]);

        $this->assertDatabaseHas('games', ['id' => $game->id]);

        // Egyedi index tesztelése: ugyanaz a két csapat ugyanakkor ne játszhasson még egyet
        $this->expectException(\Illuminate\Database\QueryException::class);
        Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => $date
        ]);
    }

    /**
     * Jegyfoglalás és "Double Booking" elleni védelem tesztelése
     */
    public function test_ticket_integrity_and_relationships()
    {
        // Alapadatok létrehozása
        $user = User::factory()->create();
        $sector = Sector::create(['sector_number' => 200, 'sector_price' => 8000]);
        $seat = Seat::create(['sector_id' => $sector->id, 'seat_number' => 50, 'row' => 1, 'col' => 1]);
        $home = Team::create(['team_name' => 'Hazai', 'team_city' => 'Város']);
        $away = Team::create(['team_name' => 'Vendég', 'team_city' => 'Város2']);
        $game = Game::create(['team_home_id' => $home->id, 'team_away_id' => $away->id, 'game_date' => now()]);

        // Jegy létrehozása
        $ticket = Ticket::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'paid'
        ]);

        // Modell kapcsolatok tesztelése
        $this->assertEquals($game->id, $ticket->game->id);
        $this->assertEquals($seat->id, $ticket->seat->id);
        $this->assertEquals($user->id, $ticket->user->id);

        // Duplikáció elleni védelem (Egy szék egy meccsre csak egyszer adható el)
        $this->expectException(\Illuminate\Database\QueryException::class);
        Ticket::create([
            'user_id' => User::factory()->create()->id, // Másik user
            'game_id' => $game->id,                     // Ugyanaz a meccs
            'seat_id' => $seat->id,                     // Ugyanaz a szék
            'status' => 'reserved'
        ]);
    }

    /**
     * SQL szintű idegen kulcs ellenőrzés (Ticket tábla)
     */
    public function test_ticket_foreign_keys_in_schema()
    {
        $dbName = env('DB_DATABASE');
        
        $foreignKeys = DB::select("
            SELECT REFERENCED_TABLE_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'tickets' 
            AND CONSTRAINT_SCHEMA = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL", [$dbName]);

        $tables = collect($foreignKeys)->pluck('REFERENCED_TABLE_NAME')->toArray();

        $this->assertContains('users', $tables);
        $this->assertContains('games', $tables);
        $this->assertContains('seats', $tables);
    }
}