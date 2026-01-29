<?php

namespace Tests\Unit;

use App\Models\Sector;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
}