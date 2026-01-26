<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sector;
use App\Models\Seat;

class SeatSeeder extends Seeder
{
    public function run(): void
{
    $sectors = \App\Models\Sector::all();

    foreach ($sectors as $sector) {
        $seats = [];
        $seatNumber = 1;

        for ($row = 1; $row <= 10; $row++) { // 10 sor szektoronként
            for ($col = 1; $col <= 15; $col++) { // 15 oszlop szektoronként
                $seats[] = [
                    'sector_id'   => $sector->id,
                    'row'         => $row,
                    'col'         => $col,
                    'seat_number' => $seatNumber++,
                ];
            }
        }
        
        // Egyszerre szúrjuk be a szektor székeit (gyorsabb)
        \App\Models\Seat::insert($seats);
    }
}
}
