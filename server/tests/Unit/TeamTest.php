<?php

namespace Tests\Unit;

use App\Models\Team;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

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
    public function test_teams_table_structure(): void
    {
        $table = 'teams';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'team_name', 'team_city'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }
    public function test_teams_table_columns_have_the_expected_types()
    {
        $table = 'teams';
        $expectedSchema = [
            'id'        => 'bigint',
            'team_name' => 'varchar',
            'team_city' => 'varchar',
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType, "A(z) '$column' típusa nem megfelelő a(z) '$table' táblában.");
        }
    }
}
