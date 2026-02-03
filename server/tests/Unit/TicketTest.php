<?php

namespace Tests\Unit;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Game;
use App\Models\Seat;
use App\Models\Team;
use App\Models\Sector;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class TicketTest extends TestCase
{
    use DatabaseTransactions;
    public function test_tickets_table_structure(): void
    {
        $table = 'tickets';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'user_id', 'game_id', 'seat_id', 'status'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }

    public function test_ticket_integrity()
    {
        $user = User::factory()->create(['email' => 'testuser_' . uniqid() . '@example.com']);
        $sector = Sector::create(['sector_number' => 5, 'sector_price' => 3000]);
        $seat = Seat::create(['sector_id' => $sector->id, 'seat_number' => 1, 'row' => 1, 'col' => 1]);
        $home = Team::create(['team_name' => 'A', 'team_city' => 'X']);
        $away = Team::create(['team_name' => 'B', 'team_city' => 'Y']);
        $game = Game::create(['team_home_id' => $home->id, 'team_away_id' => $away->id, 'game_date' => now()]);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'game_id' => $game->id,
            'seat_id' => $seat->id,
            'status' => 'paid'
        ]);

        $this->assertDatabaseHas('tickets', ['id' => $ticket->id]);
        $this->assertEquals('paid', $ticket->status);
    }
    public function test_tickets_table_columns_have_the_expected_types()
    {
        $table = 'tickets';
        $expectedSchema = [
            'id'      => 'bigint',
            'user_id' => 'bigint',
            'game_id' => 'bigint',
            'seat_id' => 'bigint',
            'status'  => 'varchar', // Itt javítottuk stringről varcharra
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType, "A(z) '$column' típusa nem megfelelő a(z) '$table' táblában.");
        }
    }
}
