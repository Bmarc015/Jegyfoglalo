<?php

namespace Tests\Unit;

use App\Models\Game;
use App\Models\Team;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
}