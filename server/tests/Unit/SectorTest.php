<?php

namespace Tests\Unit;

use App\Models\Sector;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SectorTest extends TestCase
{
    use DatabaseTransactions;

    public function test_sector_can_be_created()
    {
        $sectorId = 'T' . substr(uniqid(), -6);

        $sector = Sector::create([
            'id' => $sectorId,
            'sector_name' => $sectorId,
            'sector_price' => 12000.00,
        ]);

        $this->assertDatabaseHas('sectors', [
            'id' => $sector->id,
            'sector_name' => $sectorId,
        ]);
    }

    public function test_sectors_table_structure(): void
    {
        $table = 'sectors';
        $this->assertTrue(Schema::hasTable($table), "A '$table' tábla nem létezik.");

        $columns = ['id', 'sector_name', 'sector_price'];
        foreach ($columns as $column) {
            $this->assertTrue(Schema::hasColumn($table, $column), "A '$column' oszlop hiányzik a '$table' táblából.");
        }
    }

    public function test_sectors_table_columns_have_the_expected_types()
    {
        $table = 'sectors';
        $expectedSchema = [
            'id' => 'varchar',
            'sector_name' => 'varchar',
            'sector_price' => 'decimal',
        ];

        foreach ($expectedSchema as $column => $type) {
            $actualType = Schema::getColumnType($table, $column);
            $this->assertEquals($type, $actualType, "A(z) '$column' típusa nem megfelelő.");
        }
    }
}
