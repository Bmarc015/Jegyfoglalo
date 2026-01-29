<?php

namespace Tests\Unit;

use App\Models\Seat;
use App\Models\Sector;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

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
}