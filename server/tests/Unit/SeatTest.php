<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Seat;
use App\Models\Sector;
use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SeatTest extends TestCase
{
    use DatabaseTransactions;

    private function createGame(): Game
    {
        $home = Team::create(['team_name' => 'Hazai', 'team_city' => 'Város A']);
        $away = Team::create(['team_name' => 'Vendég', 'team_city' => 'Város B']);

        return Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => now(),
        ]);
    }

    public function test_seat_belongs_to_sector()
    {
        $sector = Sector::create(['id' => 'A1', 'sector_name' => 'A1', 'sector_price' => 5000]);
        $game = $this->createGame();
        $seat = Seat::create([
            'game_id' => $game->id,
            'sector_id' => $sector->id,
            'row' => 1,
            'col' => 1,
        ]);

        $this->assertInstanceOf(Sector::class, $seat->sector);
        $this->assertEquals($sector->id, $seat->sector_id);
    }

    public function test_seats_table_structure(): void
    {
        $table = 'seats';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'sector_id', 'game_id', 'row', 'col', 'status'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }

    public function test_seats_table_columns_have_the_expected_types()
    {
        $table = 'seats';
        $expectedSchema = [
            'id' => 'bigint',
            'sector_id' => 'varchar',
            'game_id' => 'bigint',
            'row' => 'int',
            'col' => 'int',
            'status' => 'tinyint',
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType);
        }
    }
}
