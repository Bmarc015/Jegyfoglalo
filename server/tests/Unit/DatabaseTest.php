<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Seat;
use App\Models\Sector;
use App\Models\Team;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseTest extends TestCase
{
    use DatabaseTransactions;

    private function createGame(string $date = '2026-03-15 16:00:00'): Game
    {
        $home = Team::create(['team_name' => 'Hazai ' . uniqid(), 'team_city' => 'Város']);
        $away = Team::create(['team_name' => 'Vendég ' . uniqid(), 'team_city' => 'Város2']);

        return Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => $date,
        ]);
    }

    public function test_all_tables_exist()
    {
        $tables = ['sectors', 'teams', 'seats', 'games', 'tickets', 'users'];
        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), "A(z) {$table} tábla hiányzik.");
        }
    }

    public function test_sector_and_seat_relationship()
    {
        $sectorId = 'T' . substr(uniqid(), -6);

        $sector = Sector::create([
            'id' => $sectorId,
            'sector_name' => $sectorId,
            'sector_price' => 12500.50,
        ]);
        $game = $this->createGame();

        $seat = Seat::create([
            'game_id' => $game->id,
            'sector_id' => $sector->id,
            'row' => 5,
            'col' => 10,
        ]);

        $this->assertEquals($sector->id, $seat->sector->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Seat::create([
            'game_id' => $game->id,
            'sector_id' => $sector->id,
            'row' => 5,
            'col' => 10,
        ]);
    }

    public function test_game_team_constraints()
    {
        $home = Team::create(['team_name' => 'Újszász VVSE', 'team_city' => 'Újszász']);
        $away = Team::create(['team_name' => 'Szolnok MÁV', 'team_city' => 'Szolnok']);

        $date = '2026-03-15 16:00:00';

        $game = Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => $date,
        ]);

        $this->assertDatabaseHas('games', ['id' => $game->id]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => $date,
        ]);
    }

    public function test_ticket_integrity_and_relationships()
    {
        $user = User::factory()->create();
        $sectorId = 'T' . substr(uniqid(), -6);
        $sector = Sector::create(['id' => $sectorId, 'sector_name' => $sectorId, 'sector_price' => 8000]);
        $game = $this->createGame('2026-04-20 18:00:00');
        $seat = Seat::create(['game_id' => $game->id, 'sector_id' => $sector->id, 'row' => 1, 'col' => 1]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'paid',
        ]);

        $this->assertEquals($game->id, $ticket->game->id);
        $this->assertEquals($seat->id, $ticket->seat->id);
        $this->assertEquals($user->id, $ticket->user->id);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Ticket::create([
            'user_id' => User::factory()->create()->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'reserved',
        ]);
    }

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
