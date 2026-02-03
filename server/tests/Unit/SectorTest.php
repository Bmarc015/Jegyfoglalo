<?php

namespace Tests\Unit;

use App\Models\Sector;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;

class SectorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sector_can_be_created()
    {
        $sector = Sector::create([
            'sector_number' => 302,
            'sector_price' => 12000.00
        ]);

        $this->assertDatabaseHas('sectors', ['sector_number' => 302]);
    }
    public function test_sectors_table_structure(): void
    {
        $table = 'sectors';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'sector_number', 'sector_price'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }
    public function test_sectors_table_columns_have_the_expected_types()
    {
        $table = 'sectors';
        $expectedSchema = [
            'id'            => 'bigint',
            'sector_number' => 'int',     // Itt volt a hiba
            'sector_price'  => 'decimal',
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType, "A(z) '$column' típusa nem megfelelő.");
        }
    }
}
