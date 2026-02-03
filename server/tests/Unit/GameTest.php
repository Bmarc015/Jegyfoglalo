<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Team;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class GameTest extends TestCase
{
    use DatabaseTransactions;

    public function test_game_has_home_and_away_teams()
    {
        $home = Team::create(['team_name' => 'Hazai', 'team_city' => 'Város A']);
        $away = Team::create(['team_name' => 'Vendég', 'team_city' => 'Város B']);

        $game = Game::create([
            'team_home_id' => $home->id,
            'team_away_id' => $away->id,
            'game_date' => now()
        ]);

        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }
    public function test_games_table_structure(): void
    {
        $table = 'games';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'team_home_id', 'team_away_id', 'game_date'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }
    public function test_games_table_columns_have_the_expected_types()
    {
        $table = 'games';
        $expectedSchema = [
            'id'           => 'bigint',
            'team_home_id' => 'bigint',
            'team_away_id' => 'bigint',
            'game_date'    => 'datetime',
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType, "A(z) '$column' típusa nem megfelelő a(z) '$table' táblában.");
        }
    }
}
