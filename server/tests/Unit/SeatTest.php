<?php

namespace Tests\Unit;

use App\Models\Seat;
use App\Models\Sector;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class SeatTest extends TestCase
{
    use DatabaseTransactions;

    public function test_seat_belongs_to_sector()
    {
        $sector = Sector::create(['sector_number' => 1, 'sector_price' => 5000]);
        $seat = Seat::create([
            'sector_id' => $sector->id,
            'seat_number' => 10,
            'row' => 1,
            'col' => 1
        ]);

        $this->assertInstanceOf(Sector::class, $seat->sector);
        $this->assertEquals($sector->id, $seat->sector_id);
    }
    public function test_seats_table_structure(): void
    {
        $table = 'seats';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'sector_id', 'seat_number', 'row', 'col'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }
    public function test_seats_table_columns_have_the_expected_types()
    {
        $table = 'seats';
        $expectedSchema = [
            'id'          => 'bigint',
            'sector_id'   => 'bigint',
            'seat_number' => 'int', // Javítva int-re
            'row'         => 'int', // Javítva int-re
            'col'         => 'int', // Javítva int-re
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType);
        }
    }
}
