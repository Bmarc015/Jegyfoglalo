<?php

namespace Tests\Unit;

use App\Models\Team;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class TeamTest extends TestCase
{
    use DatabaseTransactions;

    public function test_team_can_be_created()
    {
        $team = Team::create([
            'team_name' => 'Újszász VVSE',
            'team_city' => 'Újszász'
        ]);

        $this->assertDatabaseHas('teams', ['team_name' => 'Újszász VVSE']);
    }
}